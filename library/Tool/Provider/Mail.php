<?php

namespace ManipleMailer\Tool\Provider;

use ManipleMailer\MailerEvent;

class Mail extends \Zend_Tool_Framework_Provider_Abstract
{
    public function getName()
    {
        return 'mail';
    }

    /**
     * @param int $numMessages Number of messages from the queue to be sent
     * @throws \Zend_Tool_Project_Provider_Exception
     */
    public function send($numMessages = 1)
    {
        $n = (int) $numMessages;
        if ($n <= 0) {
            throw new \Zend_Tool_Project_Provider_Exception(sprintf(
                'invalid number of messages to send: %s', $numMessages
            ));
        }

        $application = maniple_bootstrap();

        /** @var \ManipleMailer\Mailer $mailer */
        $mailer = $application->getBootstrap()->getResource('Mailer.Mailer');

        $trackingConfig = $mailer->getTrackingConfig();
        printf(
            "Use %s://%s for tracking server URL\n",
            $trackingConfig['scheme'],
            $trackingConfig['host']
        );
        flush();

        $counter = 0;

        $mailer->getEventManager()->attach(MailerEvent::EVENT_SEND_PRE, function (MailerEvent $event) use (&$counter) {
            ++$counter;
            $message = $event->getMessage();
            printf('Sending message [%d] ... ', $message->getId());
            flush();
        });
        $mailer->getEventManager()->attach(MailerEvent::EVENT_SEND_ERROR, function (MailerEvent $event) {
            echo 'failed: ', $event->getParam('exception')->getMessage();
            echo "\n";
            flush();
        });
        $mailer->getEventManager()->attach(MailerEvent::EVENT_SEND, function (MailerEvent $event) {
            echo 'success', "\n"; flush();
        });

        $mailer->sendFromQueue($n);

        if (!$counter) {
            echo "Queue is empty, no mails sent.\n";
        }
    }
}
