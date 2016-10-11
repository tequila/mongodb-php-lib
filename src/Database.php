<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Command\CreateCollection;
use Tequila\MongoDB\Command\DropCollection;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListCollections;
use Tequila\MongoDB\Command\Result\CollectionInfo;
use Tequila\MongoDB\Options\DatabaseOptions;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class Database
{
    /**
     * @var Manager
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
     * @param Manager $manager
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(Manager $manager, $databaseName, array $options = [])
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
     * @inheritdoc
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        $command = new CreateCollection($this->databaseName, $collectionName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaults());

        return current($cursor->toArray());
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $command = new DropDatabase($this->databaseName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaults());

        return current($cursor->toArray());
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        $command = new DropCollection($this->databaseName, $collectionName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaults());

        return current($cursor->toArray());
    }

    /**
     * @param array $options
     * @return CollectionInfo[]
     */
    public function listCollections(array $options = [])
    {
        $command = new ListCollections($this->databaseName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaults());

        return array_map(function(array $collectionInfo) {
            return new CollectionInfo($collectionInfo);
        }, $cursor->toArray());
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
}
