<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Options\Connection\ConnectionOptions;
use Tequilla\MongoDB\Options\Driver\DriverOptions;

/**
 * Class Client
 * @package Tequilla\MongoDB
 */
class Client implements ClientInterface
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var DatabaseFactoryInterface
     */
    private $databaseFactory;

    /**
     * Client constructor.
     * @param $uri
     * @param array $options
     * @param array $driverOptions
     */
    public function __construct($uri, array $options = [], array $driverOptions = [])
    {
        $resolver = new OptionsResolver();
        ConnectionOptions::configureOptions($resolver);
        $options = $resolver->resolve($options);

        $driverOptionsResolver = new OptionsResolver();
        DriverOptions::configureOptions($driverOptionsResolver);
        $driverOptions = $driverOptionsResolver->resolve($driverOptions);

        $this->manager = new Manager((string) $uri, $options, $driverOptions);
    }

    /**
     * @return DatabaseFactoryInterface
     */
    protected function getDatabaseFactory()
    {
        if (!$this->databaseFactory) {
            $this->databaseFactory = new DatabaseFactory();
            $this->databaseFactory->setManager($this->manager);
        }
        
        return $this->manager;
    }

    public function setDatabaseFactory(DatabaseFactoryInterface $factory)
    {
        $factory->setManager($this->manager);
        $this->databaseFactory = $factory;
    }

    /**
     * @param string $name
     * @param array $options
     * @return DatabaseInterface
     */
    public function selectDatabase($name, array $options = [])
    {        
        return $this->getDatabaseFactory()->createDatabaseInstance($name, $options);
    }
    
    public function listDatabases(array $options)
    {
        // TODO: Implement listDatabases() method.
    }

    public function createCommandBuilder($databaseName)
    {
        return new CommandBuilder($this->manager, $databaseName);
    }
}