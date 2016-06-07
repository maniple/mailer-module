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
        $mailer = new Mailer($container->{'Mailer.Queue'});
        $mailer->setLogger($container->{'Log'});
        return $mailer;
    }
}
