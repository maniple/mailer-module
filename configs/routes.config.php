<?php return array(

    'mailer.messages.mark_read' => array(
        'route' => 'mailer/messages/mark-read/([_a-zA-Z0-9]+).([_a-zA-Z0-9]+)',
        'type' => 'Zend_Controller_Router_Route_Regex',
        'defaults' => array(
            'module'     => 'mailer-module',
            'controller' => 'messages',
            'action'     => 'mark-read',
        ),
        'map' => array(
            1 => 'tracking_key',
            2 => 'format',
        ),
        'reverse' => 'mailer/messages/mark-read/%s.%s',
    ),

);