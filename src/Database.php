<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequilla\MongoDB\Exception\InvalidArgumentException;

/**
 * Class Database
 * @package Tequilla\MongoDB
 */
class Database implements DatabaseInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * Database constructor.
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
     * @return ReadConcern
     */
    public function getReadConcern()
    {
        return $this->readConcern;
    }

    /**
     * @return ReadPreference
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    /**
     * @return WriteConcern
     */
    public function getWriteConcern()
    {
        return $this->writeConcern;
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
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        return $this->connection->dropCollection($this->name, $collectionName, $options);
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
     * @param  array $options
     * @return CollectionInterface
     */
    public function selectCollection($collectionName, array $options = [])
    {

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
