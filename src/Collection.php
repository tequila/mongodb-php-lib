<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Command\Aggregate;
use Tequila\MongoDB\Command\CreateIndexes;
use Tequila\MongoDB\Command\DropCollection;
use Tequila\MongoDB\Command\DropIndexes;
use Tequila\MongoDB\Command\ListIndexes;
use Tequila\MongoDB\Options\CollectionOptions;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Options\BulkWriteOptions;
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
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

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
     * @var array
     */
    private $typeMap;

    /**
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     */
    public function __construct(ManagerInterface $manager, $databaseName, $collectionName, array $options = [])
    {
        $this->manager = $manager;
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;

        $options += [
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ];

        $options = CollectionOptions::resolve($options);
        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
        $this->typeMap = $options['typeMap'];
    }

    /**
     * @param array $pipeline
     * @param array $options
     * @return AggregationCursor aggregation cursor
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        $defaults = [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap' => $this->typeMap,
        ];

        $options += $defaults;

        $command = new Aggregate($this->collectionName, $pipeline, $options);
        $cursor = $this->manager->executeCommand(
            $this->databaseName,
            $command,
            $command->getReadPreference()
        );

        return new AggregationCursor($cursor, $command->getUseCursor(), $command->getTypeMap());
    }

    /**
     * @param WriteModelInterface[] $requests
     * @param array $options
     * @return \Tequila\MongoDB\WriteResult
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;
        unset($options['writeConcern']);

        $builder = new BulkWriteBuilder();
        $builder->addMany($requests);
        $bulk = $builder->getBulk($options);
        $namespace = $this->databaseName . '.' . $this->collectionName;

        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @param array $key
     * @param array $options
     * @return string
     */
    public function createIndex(array $key, array $options = [])
    {
        $index = new Index($key, $options);

        return current($this->createIndexes([$index]));
    }

    /**
     * @param Index[] $indexes
     * @param array $options
     * @return \string[]
     */
    public function createIndexes(array $indexes, array $options = [])
    {
        $command = new CreateIndexes($this->collectionName, $indexes, $options);
        $this->executeCommand($command);

        return array_map(function(Index $index) {
            return $index->getName();
        }, $indexes);
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
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $command = new DropCollection($this->collectionName, $options);
        $cursor = $this->executeCommand($command);

        return current(iterator_to_array($cursor));
    }

    /**
     * @param array $options
     * @return array
     */
    public function dropIndexes(array $options = [])
    {
        $command = new DropIndexes($this->collectionName, '*', $options);
        $cursor = $this->executeCommand($command);

        return current(iterator_to_array($cursor));
    }

    /**
     * @param string $indexName
     * @param array $options
     * @return array
     */
    public function dropIndex($indexName, array $options = [])
    {
        $command = new DropIndexes(
            $this->databaseName,
            $this->collectionName,
            $indexName,
            $options
        );

        $cursor = $this->executeCommand($command);

        return current(iterator_to_array($cursor));
    }

    public function executeCommand(
        CommandInterface $command,
        ReadPreference $readPreference = null,
        array $typeMap = []
    ) {
        $cursor = $this->manager->executeCommand($this->databaseName, $command, $readPreference);
        $typeMap = TypeMapOptions::resolve($typeMap);
        $cursor->setTypeMap($typeMap);

        return $cursor;
    }

    /**
     * @param array $filter
     * @param array $options
     * @return Cursor
     */
    public function find(array $filter = [], array $options = [])
    {
        $defaults = [
            'readPreference' => $this->readPreference,
            'readConcern' => $this->readConcern,
            'typeMap' => $this->typeMap,
        ];
        $options += $defaults;

        return new Cursor(
            $this->manager,
            $this->databaseName,
            $this->collectionName,
            $filter,
            $options
        );
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
     * @return array
     */
    public function listIndexes()
    {
        $command = new ListIndexes($this->databaseName, $this->collectionName);
        $cursor = $command->execute($this->manager);

        return iterator_to_array($cursor);
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