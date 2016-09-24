<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Write\Bulk\BulkWrite;
use Tequila\MongoDB\Write\Bulk\BulkWriteOptions;
use Tequila\MongoDB\Write\Model\DeleteMany;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\Write\Model\WriteModelInterface;
use Tequila\MongoDB\Write\Result\DeleteResult;
use Tequila\MongoDB\Write\Result\InsertManyResult;
use Tequila\MongoDB\Write\Result\InsertOneResult;
use Tequila\MongoDB\Write\Result\UpdateResult;

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
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function drop()
    {
        return $this->connection->dropCollection($this->databaseName, $this->name);
    }

    /**
     * @param WriteModelInterface[] $requests
     * @param array $options
     * @return \Tequila\MongoDB\Write\Bulk\BulkWriteResult
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $options = $options + ['writeConcern' => $this->getWriteConcern()];
        $bulk = new BulkWrite($requests, $options);

        return $bulk->execute($this->connection, $this->databaseName, $this->name);
    }

    /**
     * @param array|object $document
     * @param array $options
     * @return InsertOneResult
     */
    public function insertOne($document, array $options = [])
    {

        $model = new InsertOne($document);
        $bulkWriteResult = $this->bulkWrite([$model], $options);

        return new InsertOneResult($bulkWriteResult);
    }

    /**
     * @param array|\Traversable $documents
     * @param array $options
     * @return InsertManyResult
     */
    public function insertMany($documents, array $options = [])
    {
        $models = [];

        foreach ($documents as $document) {
            $models[] = new InsertOne($document);
        }

        $bulkWriteResult = $this->bulkWrite($models, $options);

        return new InsertManyResult($bulkWriteResult);
    }

    /**
     * @param array|object $filter
     * @param array $options
     * @return DeleteResult
     */
    public function deleteOne($filter, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new DeleteOne($filter, $options);
        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new DeleteResult($bulkWriteResult);
    }

    /**
     * @param array|object $filter
     * @param array $options
     * @return DeleteResult
     */
    public function deleteMany($filter, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new DeleteMany($filter, $options);
        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new DeleteResult($bulkWriteResult);
    }

    /**
     * @param array|object $filter
     * @param $update
     * @param array $options
     * @return UpdateResult
     */
    public function updateOne($filter, $update, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new UpdateOne($filter, $update, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array|object $filter
     * @param $update
     * @param array $options
     * @return UpdateResult
     */
    public function updateMany($filter, $update, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new UpdateMany($filter, $update, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array|object $filter
     * @param array|object $replacement
     * @param array $options
     * @return UpdateResult
     */
    public function replaceOne($filter, $replacement, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new ReplaceOne($filter, $replacement, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array $options
     * @return array
     */
    private static function extractBulkWriteOptions(array $options)
    {
        $definedOptions = BulkWriteOptions::getDefinedOptions();
        $bulkWriteOptions = array_intersect_key($options, array_flip($definedOptions));
        $operationOptions = array_diff_key($options, $bulkWriteOptions);

        return [$bulkWriteOptions, $operationOptions];
    }
}