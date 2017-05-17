<?php

namespace Tworzenieweb\SqlProvisioner;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Luke Adamczewski
 * @package Tworzenieweb\SqlProvisioner
 */
class Application extends \Symfony\Component\Console\Application
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->boot();
    }



    private function boot()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('sql_provisioner.composer_bin_path', $this->getComposerBinPath());

        $loader = new XmlFileLoader($this->container, new FileLocator($this->getConfigPath()));
        $loader->load('services.xml');
        $this->registerCommands();
        $this->registerChecks();
        $this->container->compile();
    }

    protected function registerCommands()
    {
        foreach ($this->container->findTaggedServiceIds('console.command') as $commandId => $command) {
            $this->add($this->getCommandForId($commandId));
        }
    }



    /**
     * @return string
     */
    private function getConfigPath()
    {
        return __DIR__ . '/../config';
    }

    /**
     * @return string
     */
    private function getComposerBinPath()
    {
        return __DIR__ . '/../vendor/bin';
    }



    /**
     *
     */
    private function registerChecks()
    {
        foreach ($this->container->findTaggedServiceIds('provision.check') as $serviceId => $command) {
            $this->container->get('processor.candidate')->addCheck($this->container->get($serviceId));
        }

        foreach ($this->container->findTaggedServiceIds('provision.check.post') as $serviceId => $command) {
            $this->container->get('processor.candidate')->addPostCheck($this->container->get($serviceId));
        }
    }

    /**
     * @param string $commandId
     * @return Command
     */
    protected function getCommandForId($commandId)
    {
        if (!$this->container->has($commandId)) {
            throw new RuntimeException(sprintf('There is no command class for id %s', $commandId));
        }

        return $this->container->get($commandId);
    }
}