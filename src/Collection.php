<?php

namespace Tequilla\MongoDB;

use Tequilla\MongoDB\Write\Bulk\BulkWrite;
use Tequilla\MongoDB\Write\Bulk\BulkWriteOptions;
use Tequilla\MongoDB\Write\Model\DeleteMany;
use Tequilla\MongoDB\Write\Model\DeleteOne;
use Tequilla\MongoDB\Write\Model\InsertOne;
use Tequilla\MongoDB\Write\Model\ReplaceOne;
use Tequilla\MongoDB\Write\Model\UpdateMany;
use Tequilla\MongoDB\Write\Model\UpdateOne;
use Tequilla\MongoDB\Write\Model\WriteModelInterface;
use Tequilla\MongoDB\Write\Result\DeleteResult;
use Tequilla\MongoDB\Write\Result\InsertManyResult;
use Tequilla\MongoDB\Write\Result\InsertOneResult;
use Tequilla\MongoDB\Write\Result\UpdateResult;

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
     * @return \Tequilla\MongoDB\Write\Bulk\BulkWriteResult
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
        list($bulkOptions, $options) = $this->extractBulkWriteOptions($options);
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
        list($bulkOptions, $options) = $this->extractBulkWriteOptions($options);
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
        list($bulkOptions, $options) = $this->extractBulkWriteOptions($options);
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
        list($bulkOptions, $options) = $this->extractBulkWriteOptions($options);
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
        list($bulkOptions, $options) = $this->extractBulkWriteOptions($options);
        $model = new ReplaceOne($filter, $replacement, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array $options
     * @return array
     */
    private function extractBulkWriteOptions(array $options)
    {
        $resolver = BulkWriteOptions::getCachedResolver();
        $bulkWriteOptions = array_intersect_key($options, array_flip($resolver->getDefinedOptions()));
        $operationOptions = array_diff_key($options, $bulkWriteOptions);

        return [$bulkWriteOptions, $operationOptions];
    }
}