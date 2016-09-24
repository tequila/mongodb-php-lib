<?php

namespace Tequila\MongoDB;

/**
 * Class Database
 * @package Tequila\MongoDB
 */
class Database implements DatabaseInterface
{
    use Traits\ReadPreferenceAndConcernsTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     * @param string $databaseName
     */
    public function __construct(Connection $connection, $databaseName)
    {

        $this->connection = $connection;
        $this->name = $databaseName;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        return $this->connection->createCollection(
            $this->name,
            $collectionName,
            $options
        );
    }

    /**
     * @param string $collectionName
     * @return array
     */
    public function dropCollection($collectionName)
    {
        return $this->connection->dropCollection($this->name, $collectionName);
    }

    /**
     * @param array $options
     * @return array
     */
    public function listCollections(array $options = [])
    {
        return $this->connection->listCollections($this->name);
    }

    /**
     * @param  string $collectionName
     * @return CollectionInterface
     */
    public function selectCollection($collectionName)
    {
        $collection = new Collection($this->connection, $this->name, $collectionName);
        $collection
            ->setReadConcern($this->readConcern)
            ->setReadPreference($this->readPreference)
            ->setWriteConcern($this->writeConcern);

        return $collection;
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function drop(array $options = [])
    {
        return $this->connection->dropDatabase($this->name, $options);
    }
}
