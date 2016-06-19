<?php

namespace ManipleMailer;

use ManipleMailer\Entity\Message;
use ManipleMailer\Exception\InvalidArgumentException;
use ManipleMailer\Queue\QueueInterface;

class Mailer
{
    const EVENT_SEND = 'send';

    const EVENT_SEND_ERROR = 'send.error';

    /**
     * @var \ManipleMailer\Queue\QueueInterface
     */
    protected $_messageQueue;

    protected $_numTries = 3;

    protected $_lockTimeout = 300;

    protected $_retryTimeout = 14400;

    protected $_failPriorityDecrement = 10;

    /**
     * @var \Zend_EventManager_EventManager
     */
    protected $_eventManager;

    /**
     * @var \Zend_Log
     */
    protected $_logger;

    /**
     * @param QueueInterface $mailQueue
     */
    public function __construct(QueueInterface $mailQueue)
    {
        $this->_messageQueue = $mailQueue;
    }

    /**
     * @return \ManipleMailer\Queue\QueueInterface
     */
    public function getMessageQueue()
    {
        return $this->_messageQueue;
    }

    /**
     * @param \Zend_Log $log
     * @return \ManipleMailer\Mailer
     */
    public function setLogger(\Zend_Log $log = null)
    {
        $this->_logger = $log;
        return $this;
    }

    /**
     * @return \Zend_Log
     */
    public function getLogger()
    {
        return $this->_logger;
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
            $this->_eventManager->attach(self::EVENT_SEND, array($this, 'onSend'));
            $this->_eventManager->attach(self::EVENT_SEND_ERROR, array($this, 'onSendError'));
        }
        return $this->_eventManager;
    }

    /**
     * Sends message, reports status in the message. Entity is not persisted.
     *
     * @param \ManipleMailer\Entity\Message $message
     */
    public function send(Message $message)
    {
        $mail = new \Zefram_Mail;

        // properly display images in Thunderbird
        $mail->setType(\Zend_Mime::MULTIPART_RELATED);
        $mail->setSubject($message->getSubject());

        // TODO set From


        if (null !== ($replyTo = $message->getReplyTo())) {
            $mail->setReplyTo($replyTo->getEmail(), $replyTo->getName());
        }

        $recipient = $message->getRecipient();
        $mail->addTo($recipient->getEmail(), $recipient->getName());

        switch ($message->getContentType()) {
            case ContentType::TEXT:
                $mail->setBodyText($message->getContent());
                break;

            case ContentType::HTML:
                $html = $message->getContent();

                // set subject in <title> tag
                $html = $this->setHtmlTitle($html, $message->getSubject());

                // insert files
                $srcRegex = '/[\s]src=(?P<src>("[^"]+")|(\'[^\']+\'))/i';
                $html = preg_replace_callback($srcRegex, function ($match) use ($mail) {
                    $src = $match['src'];
                    $delim = $src[0];
                    $src = trim($src, $delim); // remove quotes
                    if (is_file($src)) {
                        // Check if this file is an image, use GD2 if available or simple
                        // type checking if GD2 is not installed
                        if (function_exists('getimagesize')) {
                            $info = getimagesize($src);
                            $type = $info ? image_type_to_mime_type($info[2]) : null;
                        } else {
                            $type = \Zefram_File_MimeType_Data::detect($src);
                        }
                        if (substr($type, 0, 6) === 'image/') {
                            // insert inline images
                            $id = \Zefram_Filter_Slug::filterStatic(basename($src, strrchr($src, '.')));
                            $attachment = $mail->attachFile($src, array(
                                'type' => $type,
                                'id' => $id,
                                'disposition' => 'inline',
                            ));
                            return sprintf(' src=%scid:%s%s', $delim, $attachment->id, $delim);
                        }
                        return $match[0];
                    }

                }, $html);

                // add tracking
                $trackingKey = bin2hex(random_bytes(16));
                $pos = stripos($html, '</body>');
                if ($pos !== false) {
                    // FIXME No globals and singletons!
                    /** @var \Zend_View $view */
                    $view = \Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('View');

                    $html = substr($html, 0, $pos)
                        . sprintf(
                            '<img src="%s" style="width:1px;height:1px;opacity:0.05" /><bgsound src="%s" volume="-10000"/>',
                            $view->serverUrl() . $view->url('mailer.messages.mark_read', array('tracking_key' => $trackingKey, 'format' => 'gif')),
                            $view->serverUrl() . $view->url('mailer.messages.mark_read', array('tracking_key' => $trackingKey, 'format' => 'mid'))
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
            if ($campaign = $message->getCampaign()) {
                $campaign->setSentMessageCount($campaign->getSentMessageCount() + 1);
            }

        } else {
            $message->setFailedAt(new \DateTime('now'));
            $message->setFailCount($message->getFailCount() + 1);

            if ($message->getFailCount() >= $this->_numTries) {
                $message->setStatus(MailStatus::FAILED);
                if ($campaign = $message->getCampaign()) {
                    $campaign->setFailedMessageCount($campaign->getFailedMessageCount() + 1);
                }
            } else {
                $message->setPriority($message->getPriority() - $this->_failPriorityDecrement);
                $message->setStatus(MailStatus::PENDING);
            }
        }

        // unlock message
        $message->setLockedAt(null);
        $message->setLockKey(null);

        // save message for logging and stats
        $this->getMessageQueue()->save($message);

        if ($exception) {
            $this->getEventManager()->trigger(self::EVENT_SEND_ERROR, $this, array(
                'exception' => $exception,
                'message' => $message,
                'mail' => $mail,
            ));
        } else {
            $this->getEventManager()->trigger(self::EVENT_SEND, $this, array(
                'message' => $message,
                'mail' => $mail,
            ));
        }
    }

    /**
     * @param string $html
     * @param string $title
     * @return string
     * @throws InvalidArgumentException
     */
    public function setHtmlTitle($html, $title)
    {
        if (preg_match('/<title[^>]*>/', $html, $match, PREG_OFFSET_CAPTURE)) {
            $offset = $match[0][1] + strlen($match[0][0]);
            if (($pos = stripos($html, '</title>', $offset)) === false) {
                throw new InvalidArgumentException('Closing tag for TITLE element not found');
            }
            $html = substr($html, 0, $offset)
                . htmlspecialchars($title)
                . substr($html, $pos);
        } else {
            if (($pos = stripos($html, '</head>')) === false) {
                throw new InvalidArgumentException('Closing tag for HEAD element not found');
            }
            $html = substr($html, 0, $pos)
                . sprintf('<title>%s</title>', htmlspecialchars($title))
                . substr($html, $pos);
        }

        return $html;
    }

    /**
     * Put mail in the queue
     *
     * @param Message $mail
     * @param int $priority OPTIONAL
     */
    public function enqueue(Message $mail, $priority = Priority::NORMAL)
    {
        $mail->setPriority((int) $priority);
        $this->getMessageQueue()->insert($mail);
    }

    /**
     * @param int $count number of messages to fetch from the queue and send
     * @param int $delay delay in milliseconds between sending consecutive messages
     */
    public function sendFromQueue($count = 1, $delay = 500)
    {
        $campaigns = array();

        foreach ($this->getMessageQueue()->fetch($count, $this->_lockTimeout, $this->_retryTimeout) as $message) {
            $this->send($message);

            if (null !== ($campaign = $message->getCampaign())) {
                $campaigns[] = $campaign;
            }

            usleep($delay * 1000);
        }
    }

    /**
     * @param \Zend_EventManager_Event $event
     * @internal
     */
    public function onSend(\Zend_EventManager_Event $event)
    {
        if (($logger = $this->getLogger()) === null) {
            return;
        }

        /** @var Message $message */
        $message = $event->getParam('message');

        $logger->info(sprintf('[mailer] Message sent to %s', $message->getRecipient()->getEmail()));
    }

    /**
     * @param \Zend_EventManager_Event $event
     * @internal
     */
    public function onSendError(\Zend_EventManager_Event $event)
    {
        if (($logger = $this->getLogger()) === null) {
            return;
        }

        /** @var Message $message */
        $message = $event->getParam('message');
        /** @var \Exception $exception */
        $exception = $event->getParam('exception');

        $logger->err(sprintf(
            '[mailer] Unable to send message to %s: %s',
            $message->getRecipient()->getEmail(),
            $exception->getMessage()
        ));
    }

    /**
     * @param array $data OPTIONAL
     * @return Message
     * @throws InvalidArgumentException
     */
    public function createMessage(array $data = null)
    {
        $message = new Message();

        if ($data) {
            foreach ($data as $key => $value) {
                switch (strtolower($key)) {
                    case 'recipient':
                        if (is_array($value)) {
                            $message->setRecipient($value['email'], $value['name']);
                        } else {
                            $message->setRecipient($value);
                        }
                        break;

                    case 'replyto':
                        if (is_array($value)) {
                            $message->setReplyTo($value['email'], $value['name']);
                        } else {
                            $message->setReplyTo($value);
                        }
                        break;

                    default:
                        $method = 'set' . $key;
                        if (method_exists($message, $method)) {
                            $message->{$method}($value);
                        } else {
                            throw new InvalidArgumentException('Unsupported message property: %s', $key);
                        }
                        break;
                }
            }
        }

        return $message;
    }
}
