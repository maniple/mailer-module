<?php return array(
    'Mailer.Queue' => array(
        'callback' => 'MailerModule\\Service\\QueueFactory::createService',
    ),
    'Mailer.Mailer' => array(
        'callback' => 'MailerModule\\Service\\MailerFactory::createService',
    ),

    'Mailer' => 'resource:Mailer.Mailer',
);
