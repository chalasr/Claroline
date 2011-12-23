<?php

namespace Claroline\PluginBundle\Installer;

use Claroline\PluginBundle\Installer\Loader;
use Claroline\PluginBundle\Installer\Validator\Validator;
use Claroline\PluginBundle\Installer\Migrator;
use Claroline\PluginBundle\Installer\Recorder\Recorder;
use Claroline\PluginBundle\Exception\InstallationException;
use Symfony\Component\HttpKernel\KernelInterface;

class Installer
{
    private $loader; 
    private $validator;
    private $recorder;
    private $migrator;
    private $kernel;

    public function __construct(
        Loader $loader,
        Validator $validator,
        Migrator $migrator,
        Recorder $recorder,
        KernelInterface $kernel
    )
    {
        $this->loader = $loader;
        $this->validator = $validator;
        $this->migrator = $migrator;
        $this->recorder = $recorder;  
        $this->kernel = $kernel;
    }
    
    public function setLoader(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function setRecorder(Recorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public function setMigrator(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }
    
    public function install($pluginFQCN)
    {
        $this->checkRegistrationStatus($pluginFQCN, false);
        $plugin = $this->loader->load($pluginFQCN);        
        $this->validator->validate($plugin);
        $this->migrator->install($plugin);
        $this->recorder->register($plugin);
        $this->kernel->shutdown();
        $this->kernel->boot();
    }

    public function uninstall($pluginFQCN)
    {
        $this->checkRegistrationStatus($pluginFQCN, true);
        $plugin = $this->loader->load($pluginFQCN);
        $this->recorder->unregister($plugin);
        $this->migrator->remove($plugin);
        $this->kernel->shutdown();
        $this->kernel->boot();
    }
    
    public function isInstalled($pluginFQCN)
    {
        return $this->recorder->isRegistered($pluginFQCN);
    }
    
    private function checkRegistrationStatus($pluginFQCN, $expectedStatus)
    {
        if ($this->isInstalled($pluginFQCN) !== $expectedStatus)
        {
            $expectedStatus === true ? $stateDiscr = 'not' : $stateDiscr = 'already';
            
            throw new InstallationException(
                "Plugin '{$pluginFQCN}' is {$stateDiscr} registered."
            );
        }
    }
}