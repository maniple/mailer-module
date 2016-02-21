<?php

class MailerModule_Bootstrap extends Maniple_Application_Module_Bootstrap
{
    public function getResourceConfig()
    {
        return require __DIR__ . '/configs/resources.config.php';
    }

    protected function _initEntityManager()
    {
        $bootstrap = $this->getApplication();

        /** @var ManipleCore\Doctrine\Config $config */
        $config = $bootstrap->getResource('EntityManager.config');
        $config->addPath(__DIR__ . '/library/Entity');
    }
}
