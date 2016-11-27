<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Command\CreateCollectionResolver;
use Tequila\MongoDB\Command\DropCollection;
use Tequila\MongoDB\Command\DropDatabaseResolver;
use Tequila\MongoDB\Command\ListCollections;
use Tequila\MongoDB\Command\Result\CollectionInfo;
use Tequila\MongoDB\Options\DatabaseOptions;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Traits\CommandBuilderTrait;

class Database
{
    use CommandBuilderTrait;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var ReadConcern|null
     */
    private $readConcern;

    /**
     * @var ReadPreference|null
     */
    private $readPreference;

    /**
     * @var WriteConcern|null
     */
    private $writeConcern;

    /**
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(ManagerInterface $manager, $databaseName, array $options = [])
    {
        $this->manager = $manager;
        $this->databaseName = $databaseName;

        $options += [
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ];

        $options = DatabaseOptions::resolve($options);

        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        $cursor = $this->executeCommand(
            ['create' => $collectionName],
            $options,
            CreateCollectionResolver::class
        );

        return $cursor->current();
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $cursor = $this->executeCommand(
            ['dropDatabase' => 1],
            $options,
            DropDatabaseResolver::class
        );

        return $cursor->current();
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        $command = new DropCollection($collectionName, $options);
        $cursor = $this->runCommand($command);

        return current(iterator_to_array($cursor));
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param CommandInterface $command
     * @param ReadPreference|null $readPreference
     * @return CursorInterface
     */
    public function runCommand(CommandInterface $command, ReadPreference $readPreference = null)
    {
        return $this->manager->executeCommand($this->databaseName, $command, $readPreference);
    }

    /**
     * @param array $options
     * @return CollectionInfo[]
     */
    public function listCollections(array $options = [])
    {
        $command = new ListCollections($options);
        $cursor = $this->runCommand($command);

        return array_map(function(array $collectionInfo) {
            return new CollectionInfo($collectionInfo);
        }, iterator_to_array($cursor));
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return Collection
     */
    public function selectCollection($collectionName, array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'writeConcern' => $this->writeConcern,
        ];

        return new Collection($this->manager, $this->databaseName, $collectionName, $options);
    }

    /**
     * @param array $command
     * @param array $options
     * @param $resolverClass
     * @return CursorInterface
     */
    private function executeCommand(array $command, array $options, $resolverClass)
    {
        $cursor = $this->commandBuilder
            ->createCommand($command, $options, $resolverClass)
            ->execute($this->manager, $this->databaseName);

        $cursor->setTypeMap(TypeMapOptions::getDefault());

        return $cursor;
    }
}
