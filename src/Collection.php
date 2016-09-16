<?php

namespace Tequilla\MongoDB;

use Tequilla\MongoDB\BulkWrite\BulkWrite;
use Tequilla\MongoDB\WriteModel\WriteModelInterface;

class Collection
{
    use Traits\ReadPreferenceAndConcernsTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $name;

    /**
     * @param Connection $connection
     * @param string $databaseName
     * @param string $collectionName
     */
    public function __construct(Connection $connection, $databaseName, $collectionName)
    {
        $this->connection = $connection;
        $this->databaseName = $databaseName;
        $this->name = $collectionName;
    }

    /**
     * @param WriteModelInterface[] $requests
     * @param array $options
     * @return \Tequilla\MongoDB\BulkWrite\BulkWriteResult
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $bulk = new BulkWrite($requests, $options);

        return $bulk->execute($this->connection, $this->databaseName, $this->name);
    }
}