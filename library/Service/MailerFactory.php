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
        $config = $container->{'Config'};

        $mailer = new Mailer($container->{'Mailer.Queue'});
        $mailer->setLogger($container->{'Log'});

        if (isset($config['mailer']['tracking'])) {
            $mailer->setTrackingConfig($config['mailer']['tracking']);
        }

        return $mailer;
    }
}
