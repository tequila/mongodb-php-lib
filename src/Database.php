<?php

namespace Tequilla\MongoDB;

/**
 * Class Database
 * @package Tequilla\MongoDB
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
