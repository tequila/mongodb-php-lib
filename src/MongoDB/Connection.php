<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Command\Type\CreateCollectionType;
use Tequilla\MongoDB\Command\Type\DropCollectionType;
use Tequilla\MongoDB\Command\Type\DropDatabaseType;
use Tequilla\MongoDB\Command\Type\ListCollectionsType;
use Tequilla\MongoDB\Command\Type\ListDatabasesType;
use Tequilla\MongoDB\Event\DatabaseCommandEvent;
use Tequilla\MongoDB\Event\DatabaseDroppedEvent;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Exception\UnexpectedResultException;
use Tequilla\MongoDB\Options\Connection\ConnectionOptions;
use Tequilla\MongoDB\Options\Driver\DriverOptions;
use Tequilla\MongoDB\Options\Driver\TypeMapOptions;
use Tequilla\MongoDB\Event\GetDatabaseEvent;
use Tequilla\MongoDB\Event\DropDatabaseEvent;
use Tequilla\MongoDB\Event\DatabaseEvent;
use Tequilla\MongoDB\assertValidDatabaseName;
use Tequilla\MongoDB\assertValidCollectionName;
use Tequilla\MongoDB\assertValidNamespace;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Client
 * @package Tequilla\MongoDB
 */
class Connection
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var CommandBuilder[]
     */
    private $commandBuilders = [];

    /**
     * @var EventDispatcherInterface|null
     */
    private $dispatcher;

    /**
     * Client constructor.
     * @param string $uri
     * @param array $options
     * @param array $driverOptions
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        $uri = 'mongodb://localhost:27017',
        array $options = [],
        array $driverOptions = [],
        EventDispatcherInterface $dispatcher = null
    ) {
        $resolver = new OptionsResolver();
        ConnectionOptions::configureOptions($resolver);
        $options = $resolver->resolve($options);

        $driverOptionsResolver = new OptionsResolver();
        DriverOptions::configureOptions($driverOptionsResolver);
        $driverOptions = $driverOptionsResolver->resolve($driverOptions);

        $this->manager = new Manager((string) $uri, $options, $driverOptions);
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return \MongoDB\Driver\ReadConcern
     */
    public function getReadConcern()
    {
        return $this->manager->getReadConcern();
    }

    /**
     * @return \MongoDB\Driver\WriteConcern
     */
    public function getWriteConcern()
    {
        return $this->manager->getWriteConcern();
    }

    /**
     * @return \MongoDB\Driver\ReadPreference
     */
    public function getReadPreference()
    {
        return $this->manager->getReadPreference();
    }

    /**
     * @param string $namespace
     * @param BulkWrite $bulk
     * @param WriteConcern|null $writeConcern
     * @return \MongoDB\Driver\WriteResult
     */
    public function executeBulkWrite($namespace, BulkWrite $bulk, WriteConcern $writeConcern = null)
    {
        assertValidNamespace($namespace);

        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @param string $databaseName
     * @param array|object $command
     * @param ReadPreference|null $readPreference
     * @return array
     */
    public function executeCommand($databaseName, $command, ReadPreference $readPreference = null)
    {
        assertValidDatabaseName($databaseName);

        if (!is_array($command) && !is_object($command)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$command must be an array or an object, %s given',
                    getType($command)
                )
            );
        }

        $command = (array) $command;

        if (empty($command)) {
            throw new InvalidArgumentException('$command must not be empty.');
        }

        if (!$readPreference) {
            $readPreference = $this->getReadPreference();
        }

        $server = $this->manager->selectServer($readPreference);

        if ($this->dispatcher) {
            $event = new DatabaseCommandEvent($databaseName, $command, $server);
            $this->dispatcher->dispatch(Events::BEFORE_DATABASE_COMMAND_EXECUTED, $event);
        }

        $driverCommand = new Command($command);

        $cursor = $server->executeCommand($databaseName, $driverCommand);
        $result = TypeMapOptions::setArrayTypeMapOnCursor($cursor)->toArray();

        if ($this->dispatcher && isset($event)) {
            $event->refreshPropagation();
            $event->setCommandResult($result);
            $this->dispatcher->dispatch(Events::DATABASE_COMMAND_EXECUTED, $event);
        }

        return $result;
    }

    /**
     * @param string $namespace
     * @param Query $query
     * @param ReadPreference|null $readPreference
     * @return \MongoDB\Driver\Cursor
     */
    public function executeQuery($namespace, Query $query, ReadPreference $readPreference = null)
    {
        assertValidNamespace($namespace);

        return $this->manager->executeQuery($namespace, $query, $readPreference);
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($databaseName, $collectionName, array $options = [])
    {
        assertValidDatabaseName($databaseName);
        assertValidCollectionName($collectionName);
        
        $options['create'] = $collectionName;

        return $this->buildAndExecuteCommand($databaseName, CreateCollectionType::class, $options);
    }

    /**
     * @param $databaseName
     * @param $collectionName
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function dropCollection($databaseName, $collectionName, array $options = [])
    {
        $options['drop'] = (string) $collectionName;

        return $this->buildAndExecuteCommand($databaseName, DropCollectionType::class, $options);
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function listCollections($databaseName, array $options = [])
    {
        return $this->buildAndExecuteCommand($databaseName, ListCollectionsType::class, $options);
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        assertValidDatabaseName($databaseName);

        if ($this->dispatcher) {
            $event = new DropDatabaseEvent($this, $databaseName, $options);
            $this->dispatcher->dispatch(Events::BEFORE_DATABASE_DROPPED, $event);
        }

        $result = $this->buildAndExecuteCommand($databaseName, DropDatabaseType::class, $options);

        if ($this->dispatcher) {
            $event = new DatabaseDroppedEvent($this, $databaseName, $result);
            $this->dispatcher->dispatch(Events::DATABASE_DROPPED, $event);
        }

        return $result;
    }

    /**
     * @param array $options
     * @return array
     */
    public function listDatabases(array $options = [])
    {
        $result = $this->buildAndExecuteCommand('admin', ListDatabasesType::class, $options);

        if (!isset($result[0]['databases'])) {
            throw new UnexpectedResultException(
                sprintf(
                    'ListDatabases MongoDB command did not return expected result. Actual result: "%s"',
                    print_r($result, true)
                )
            );
        }

        return $result[0]['databases'];
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return DatabaseInterface
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        assertValidDatabaseName($databaseName);

        $database = null;

        if ($this->dispatcher) {
            $event = new GetDatabaseEvent($this, $databaseName, $options);
            $this->dispatcher->dispatch(Events::BEFORE_DATABASE_SELECTED, $event);
            if (null !== $event->getDatabase()) {
                $database = $event->getDatabase();
            }
            $options = $event->getOptions();
        }

        if (null === $database) {
            $database = new Database($this->manager, $databaseName, $options);
        }

        if ($this->dispatcher) {
            $event = new DatabaseEvent($database);
            $this->dispatcher->dispatch(Events::DATABASE_SELECTED, $event);
        }

        return $database;
    }

    public function selectCollection()
    {

    }

    /**
     * @param string $databaseName
     * @return CommandBuilder
     */
    public function createCommandBuilder($databaseName)
    {
        assertValidDatabaseName($databaseName);

        if (!isset($this->commandBuilders[$databaseName])) {
            $this->commandBuilders[$databaseName] = new CommandBuilder($this, $databaseName);
        }

        return $this->commandBuilders[$databaseName];
    }

    /**
     * @param string $databaseName
     * @param string $commandTypeClass
     * @param array $options
     * @return array
     */
    private function buildAndExecuteCommand($databaseName, $commandTypeClass, array $options)
    {
        return $this
            ->createCommandBuilder($databaseName)
            ->buildCommand((string) $commandTypeClass)
            ->execute($options);
    }
}
