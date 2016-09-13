<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\Type\DropIndexesType;
use Tequilla\MongoDB\Command\Type\ListIndexesType;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Command\Type\CreateCollectionType;
use Tequilla\MongoDB\Command\Type\CreateIndexesType;
use Tequilla\MongoDB\Command\Type\DropCollectionType;
use Tequilla\MongoDB\Command\Type\DropDatabaseType;
use Tequilla\MongoDB\Command\Type\ListCollectionsType;
use Tequilla\MongoDB\Command\Type\ListDatabasesType;
use Tequilla\MongoDB\Event\DatabaseCommandEvent;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Exception\UnexpectedResultException;
use Tequilla\MongoDB\Options\Connection\ConnectionOptions;
use Tequilla\MongoDB\Options\Driver\DriverOptions;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tequilla\MongoDB\Util\StringUtils;
use Tequilla\MongoDB\Util\TypeUtils;

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

        $this->manager = new Manager((string)$uri, $options, $driverOptions);
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
     * @param string $databaseName
     * @param string $collectionName
     * @param BulkWrite $bulk
     * @param WriteConcern|null $writeConcern
     * @return \MongoDB\Driver\WriteResult
     */
    public function executeBulkWrite($databaseName, $collectionName, BulkWrite $bulk, WriteConcern $writeConcern = null)
    {
        $namespace = StringUtils::createNamespace($databaseName, $collectionName);

        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @param string $databaseName
     * @param array $commandOptions
     * @param ReadPreference|null $readPreference
     * @return CommandCursor
     */
    public function executeCommand($databaseName, $commandOptions, ReadPreference $readPreference = null)
    {
        StringUtils::ensureValidDatabaseName($databaseName);

        if (!is_array($commandOptions) && !is_object($commandOptions)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$commandOptions must be an array or an object, %s given',
                    TypeUtils::getType($commandOptions)
                )
            );
        }

        if ($commandOptions instanceof Command) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s does not accept %s instances in order to have access to the command options',
                    Command::class,
                    __METHOD__
                )
            );
        }

        $commandOptions = (array) $commandOptions;

        if (empty($commandOptions)) {
            throw new InvalidArgumentException('$command must not be empty.');
        }

        if (!$readPreference) {
            $readPreference = $this->getReadPreference();
        }

        $server = $this->manager->selectServer($readPreference);

        if ($this->dispatcher) {
            $event = new DatabaseCommandEvent($databaseName, $commandOptions, $server);
            $this->dispatcher->dispatch(Events::BEFORE_DATABASE_COMMAND_EXECUTED, $event);
        }

        $driverCommand = new Command($commandOptions);

        $mongoCursor = $server->executeCommand($databaseName, $driverCommand);
        $cursor = new CommandCursor($mongoCursor);

        if ($this->dispatcher) {
            $event = new DatabaseCommandEvent($databaseName, $commandOptions, $server);
            $event->setCursor($cursor);
            $this->dispatcher->dispatch(Events::DATABASE_COMMAND_EXECUTED, $event);
        }

        return $cursor;
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param Query $query
     * @param ReadPreference|null $readPreference
     * @return \MongoDB\Driver\Cursor
     */
    public function executeQuery($databaseName, $collectionName, Query $query, ReadPreference $readPreference = null)
    {
        $namespace = StringUtils::createNamespace($databaseName, $collectionName);

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
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        $options[CreateCollectionType::getCommandName()] = $collectionName;

        return $this
            ->buildAndExecuteCommand($databaseName, CreateCollectionType::class, $options)
            ->toArray();
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($databaseName, $collectionName, array $options = [])
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        $options[DropCollectionType::getCommandName()] = (string)$collectionName;

        return $this
            ->buildAndExecuteCommand($databaseName, DropCollectionType::class, $options)
            ->toArray();
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function listCollections($databaseName, array $options = [])
    {
        return $this
            ->buildAndExecuteCommand($databaseName, ListCollectionsType::class, $options)
            ->toArray();
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param Index[] $indexes
     * @return array
     */
    public function createIndexes($databaseName, $collectionName, array $indexes)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes array cannot be empty');
        }

        $options = [CreateIndexesType::getCommandName() => $collectionName, 'indexes' => []];

        foreach ($indexes as $i => $index) {
            if (!$index instanceof Index) {
                throw new InvalidArgumentException(
                    sprintf(
                        '$indexes[%d] must be an Index instance, %s given',
                        $i,
                        TypeUtils::getType($index)
                    )
                );
            }

            $options['indexes'][] = ['key' => $index->getKeys()] + $index->getOptions();
        }

        $this->buildAndExecuteCommand($databaseName, CreateIndexesType::class, $options);

        return array_map(
            function (Index $index) {
                return $index->getName();
            },
            $indexes
        );
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param Index $index
     * @return string
     */
    public function createIndex($databaseName, $collectionName, Index $index)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        $result = $this->createIndexes($databaseName, $collectionName, [$index]);

        return current($result);
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @return CommandCursor
     */
    public function listIndexes($databaseName, $collectionName)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        return $this->buildAndExecuteCommand(
            $databaseName,
            ListIndexesType::class,
            [ListIndexesType::getCommandName() => $collectionName]
        );
    }

    /**
     * Drops a single index in the collection
     *
     * @param string $databaseName
     * @param string $collectionName
     * @param string|array|Index
     * @return array
     */
    public function dropIndex($databaseName, $collectionName, $index)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        if (is_string($index)) {
            $indexName = $index;
        } else if ($index instanceof Index) {
            $indexName = $index->getName();
        } else if (is_array($index)) {
            $indexName = (new Index($index))->getName();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    '$index must be a string, Index instance or array of index keys, %s given',
                    TypeUtils::getType($index)
                )
            );
        }

        $cursor = $this->buildAndExecuteCommand(
            $databaseName,
            DropIndexesType::class,
            [DropIndexesType::getCommandName() => $collectionName, 'index' => $indexName]
        );

        return $cursor->toArray();
    }

    /**
     * Drops all indexes in the collection
     *
     * @param string $databaseName
     * @param string $collectionName
     * @return array
     */
    public function dropIndexes($databaseName, $collectionName)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);

        $cursor = $this->buildAndExecuteCommand(
            $databaseName,
            DropIndexesType::class,
            [DropIndexesType::getCommandName() => $collectionName, 'index' => '*']
        );

        return $cursor->toArray();
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        StringUtils::ensureValidDatabaseName($databaseName);

        $cursor = $this->buildAndExecuteCommand($databaseName, DropDatabaseType::class, $options);

        return $cursor->toArray();
    }

    /**
     * @param array $options
     * @return array
     */
    public function listDatabases(array $options = [])
    {
        $cursor = $this->buildAndExecuteCommand('admin', ListDatabasesType::class, $options);
        $result = $cursor->toArray();

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
        StringUtils::ensureValidDatabaseName($databaseName);

        return new Database($this, $databaseName, $options);
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     */
    public function selectCollection($databaseName, $collectionName)
    {
        StringUtils::ensureValidDatabaseName($databaseName);
        StringUtils::ensureValidCollectionName($collectionName);
    }

    /**
     * @param string $databaseName
     * @return CommandBuilder
     */
    public function createCommandBuilder($databaseName)
    {
        StringUtils::ensureValidDatabaseName($databaseName);

        if (!isset($this->commandBuilders[$databaseName])) {
            $this->commandBuilders[$databaseName] = new CommandBuilder($this, $databaseName);
        }

        return $this->commandBuilders[$databaseName];
    }

    /**
     * @param string $databaseName
     * @param string $commandTypeClass
     * @param array $options
     * @return CommandCursor
     */
    private function buildAndExecuteCommand($databaseName, $commandTypeClass, array $options)
    {
        return $this
            ->createCommandBuilder($databaseName)
            ->buildCommand((string)$commandTypeClass)
            ->execute($options);
    }
}
