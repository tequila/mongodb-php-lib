<?php

namespace Tequilla\MongoDB;

use Tequilla\MongoDB\BulkWrite\BulkWrite;
use Tequilla\MongoDB\WriteModel\WriteModelInterface;

class Collection implements CollectionInterface
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
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $bulk = new BulkWrite($requests, $options);
        $bulk->compile();

        $this->connection->executeBulkWrite(
            $this->databaseName,
            $this->name,
            $bulk->getBulk()
        );
    }
}