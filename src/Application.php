<?php

namespace Tworzenieweb\SqlProvisioner;

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



    private function registerChecks()
    {
        foreach ($this->container->findTaggedServiceIds('provision.check') as $serviceId => $command) {
            $this->container->get('processor.candidate')->addCheck($this->container->get($serviceId));
        }
    }

    /**
     * @param string $commandId
     * @return Command
     */
    protected function getCommandForId($commandId)
    {
        return $this->container->get($commandId);
    }
}