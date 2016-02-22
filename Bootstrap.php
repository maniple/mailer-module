<?php

class MailerModule_Bootstrap extends Maniple_Application_Module_Bootstrap
{
    public function getResourceConfig()
    {
        return require __DIR__ . '/configs/resources.config.php';
    }

    protected function _initRouter()
    {
        /** @var Zend_Application_Bootstrap_BootstrapAbstract $bootstrap */
        $bootstrap = $this->getApplication();
        $bootstrap->bootstrap('FrontController');

        /** @var Zend_Controller_Router_Rewrite $router */
        $router = $bootstrap->getResource('FrontController')->getRouter();
        $router->addConfig(new Zend_Config(require __DIR__ . '/configs/routes.config.php'));
    }

    protected function _initEntityManager()
    {
        $bootstrap = $this->getApplication();

        /** @var ManipleCore\Doctrine\Config $config */
        $config = $bootstrap->getResource('EntityManager.config');
        $config->addPath(__DIR__ . '/library/Entity');
    }
}
