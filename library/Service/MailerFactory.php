<?php

namespace MailerModule\Service;

use MailerModule\Mailer;

abstract class MailerFactory
{
    /**
     * @param object $container
     */
    public static function createService($container)
    {
        $mailer = new Mailer();
        $mailer->setMessageQueue($container->{'Mailer.Queue'});
        return $mailer;
    }
}
