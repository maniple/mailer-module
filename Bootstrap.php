<?php

class ManipleMailer_Bootstrap extends Maniple_Application_Module_Bootstrap
{
    public function getModuleDependencies()
    {
        return array('maniple-user', 'maniple-doctrine'); // entityManager.config
    }

    public function getResourceConfig()
    {
        return require __DIR__ . '/configs/resources.config.php';
    }

    public function getRoutesConfig()
    {
        return require __DIR__ . '/configs/routes.config.php';
    }

    protected function _initEntityManager()
    {
        $bootstrap = $this->getApplication();

        /** @var ManipleDoctrine\Config $config */
        $config = $bootstrap->getResource('EntityManager.config');
        $config->addPath(__DIR__ . '/library/Entity');
    }

    protected function _initView()
    {
        // ensure View is bootstrapped as it's in Mailer::send()
        $this->getApplication()->bootstrap('View');
    }
}
