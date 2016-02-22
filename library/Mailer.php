<?php

namespace MailerModule;

use MailerModule\Entity\Message;
use MailerModule\Queue\QueueInterface;

class Mailer
{
    /**
     * @var \MailerModule\Queue\QueueInterface
     */
    protected $_messageQueue;

    protected $_numRetries = 3;

    /**
     * @var \Zend_EventManager_EventManager
     */
    protected $_eventManager;

    /**
     * @param \MailerModule\Queue\QueueInterface $mailQueue
     * @return \MailerModule\Mailer
     */
    public function setMessageQueue(QueueInterface $mailQueue)
    {
        $this->_messageQueue = $mailQueue;
        return $this;
    }

    /**
     * @return \MailerModule\Queue\QueueInterface
     * @throws \Exception
     */
    public function getMessageQueue()
    {
        if (!$this->_messageQueue) {
            throw new \Exception('Mail queue has not been provided');
        }
        return $this->_messageQueue;
    }

    /**
     * @return \Zend_EventManager_EventManager
     */
    public function getEventManager()
    {
        if (!$this->_eventManager) {
            $this->_eventManager = new \Zend_EventManager_EventManager(array(
                __CLASS__,
                get_class($this),
            ));
        }
        return $this->_eventManager;
    }

    /**
     * Sends message, reports status in the message. Entity is not persisted.
     *
     * @param \MailerModule\Entity\Message $message
     */
    public function send(Message $message)
    {
        $mail = new \Zefram_Mail;

        // properly display images in Thunderbird
        $mail->setType(\Zend_Mime::MULTIPART_RELATED);
        $mail->setSubject($message->getSubject());

        if ($message->getReplyToEmail()) {
            $mail->setReplyTo(
                $message->getReplyToEmail(),
                $message->getReplyToName()
            );
        }

        foreach ($message->getRecipients() as $recipient) {
            /** @var \MailerModule\Entity\Recipient $recipient */
            switch ($recipient->getType()->getValue()) {
                case RecipientType::TO:
                    $mail->addTo($recipient->getEmail(), $recipient->getName());
                    break;

                case RecipientType::CC:
                    $mail->addCc($recipient->getEmail(), $recipient->getName());
                    break;

                case RecipientType::BCC:
                    $mail->addBcc($recipient->getEmail());
                    break;
            }
        }

        switch ($message->getContentType()) {
            case ContentType::TEXT:
                $mail->setBodyText($message->getContent());
                break;

            case ContentType::HTML:
                // Add tracking
                $trackingKey = bin2hex(random_bytes(16));
                $html = $message->getContent();
                $pos = stripos('</body>', $html);
                if ($pos !== false) {
                    /** @var \Zend_View $view */
                    $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('View');


                    $html = substr($html, 0, $pos)
                        . sprintf(
                            '<img src="%s" /><bgsound src="%s" volume="-10000" />',
                            $view->url('mailer.messages.mark_read', array('tracking_key' => $trackingKey, 'format' => 'gif')),
                            $view->url('mailer.messages.mark_read', array('tracking_key' => $trackingKey, 'format' => 'mid'))
                          )
                        . substr($html, $pos);
                }
                $message->setTrackingKey($trackingKey);
                $mail->setBodyHtml($html);
                break;
        }

        $this->getEventManager()->trigger('send.pre', $this, array(
            'message' => $message,
            'mail' => $mail,
        ));

        try {
            $mail->send();
            $exception = null;

        } catch (\Exception $exception) {
        }

        if (empty($exception)) {
            $message->setStatus(MailStatus::SENT);
            $message->setSentAt(new \DateTime('now'));

        } else {
            // TODO Log error
            $message->setFailCount($message->getFailCount() + 1);

            if ($message->getFailCount() >= $this->_numRetries) {
                $message->setStatus(MailStatus::FAILED);
            } else {
                $message->setPriority($message->getPriority() - 1);
                $message->setStatus(MailStatus::PENDING);
            }
        }

        // unlock message
        $message->setLockedAt(null);
        $message->setLockKey(null);

        if ($exception) {
            $this->getEventManager()->trigger('send.error', $this, array(
                'exception' => $exception,
                'message' => $message,
                'mail' => $mail,
            ));
        } else {
            $this->getEventManager()->trigger('send', $this, array(
                'message' => $message,
                'mail' => $mail,
            ));
        }
    }

    /**
     * Put mail in the queue
     *
     * @param Message $mail
     * @throws \Exception
     */
    public function enqueue(Message $mail)
    {
        $this->getMessageQueue()->enqueue($mail);
    }

    public function sendFromQueue($count = 1)
    {
        $campaigns = array();

        foreach ($this->getMessageQueue()->dequeue($count) as $message) {
            $this->send($message);

            // TODO what if campaign has changed? That leaves us with the invalid counters on the old campaign
            // Add lifecycle event listener to EventManager:
            // http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/events.html

            $this->getMessageQueue()->save($message);

            if (null !== ($campaign = $message->getCampaign())) {
                $campaigns[] = $campaign;
            }

            usleep(500000);
        }

        $this->getMessageQueue()->refreshCampaignCounters($campaigns);
    }
}
