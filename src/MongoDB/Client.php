<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Command\Type\ListDatabasesType;
use Tequilla\MongoDB\Options\Connection\ConnectionOptions;
use Tequilla\MongoDB\Options\Driver\DriverOptions;
use Tequilla\MongoDB\Options\Driver\TypeMapOptions;

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
     * @var CommandBuilder[]
     */
    private $commandBuilders = [];

    /**
     * Client constructor.
     * @param $uri
     * @param array $options
     * @param array $driverOptions
     */
    public function __construct($uri = 'mongodb://localhost:27017', array $options = [], array $driverOptions = [])
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

        return $this->databaseFactory;
    }

    /**
     * @param DatabaseFactoryInterface $factory
     */
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

    /**
     * @param array $options
     * @return array
     */
    public function listDatabases(array $options = [])
    {
        $cursor = $this->createCommandBuilder('admin')
            ->buildCommand(ListDatabasesType::class)
            ->execute($options);

        return TypeMapOptions::setArrayTypeMapOnCursor($cursor)->toArray()[0]['databases'];
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $cursor = $this
            ->createCommandBuilder($databaseName)
            ->buildCommand(DropDatabaseType::class)
            ->execute($options);

        return TypeMapOptions::setArrayTypeMapOnCursor($cursor)->toArray();
    }

    /**
     * @param $databaseName
     * @return CommandBuilder
     */
    public function createCommandBuilder($databaseName)
    {
        if (!$this->commandBuilders[$databaseName]) {
            $this->commandBuilders[$databaseName] = new CommandBuilder($this->manager, $databaseName);
        }

        return $this->commandBuilders[$databaseName];
    }
}
