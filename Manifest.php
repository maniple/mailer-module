<?php

class ManipleMailer_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable
{
    public function getProviders()
    {
        return require __DIR__ . '/configs/providers.config.php';
    }
}
