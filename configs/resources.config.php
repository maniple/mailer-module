<?php return array(
    'Mailer.Queue' => array(
        'callback' => 'ManipleMailer\\Service\\QueueFactory::createService',
    ),
    'Mailer.Mailer' => array(
        'callback' => 'ManipleMailer\\Service\\MailerFactory::createService',
    ),

    'Mailer' => 'resource:Mailer.Mailer',
);
