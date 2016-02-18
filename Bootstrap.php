<?php

class MailerModule_Bootstrap extends Maniple_Application_Module_Bootstrap
{
    protected function _initEntityManager()
    {
        $bootstrap = $this->getApplication();

        /** @var ManipleCore\Doctrine\Config $config */
        $config = $bootstrap->getResource('EntityManager.config');
        $config->addPath(__DIR__ . '/library/Entity');
    }
}
