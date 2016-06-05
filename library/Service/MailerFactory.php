<?php

namespace ManipleMailer\Service;

use ManipleMailer\Mailer;

abstract class MailerFactory
{
    /**
     * @param object $container
     * @return Mailer
     */
    public static function createService($container)
    {
        $mailer = new Mailer();
        $mailer->setMessageQueue($container->{'Mailer.Queue'});
        return $mailer;
    }
}
