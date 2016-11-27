<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Command\AggregateResolver;
use Tequila\MongoDB\Command\CountResolver;
use Tequila\MongoDB\Command\CreateIndexesResolver;
use Tequila\MongoDB\Command\DropCollection;
use Tequila\MongoDB\Command\DropIndexes;
use Tequila\MongoDB\Command\ListIndexes;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\CollectionOptions;
use Tequila\MongoDB\Options\BulkWriteOptions;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Traits\CommandBuilderTrait;
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
     * @var string
     */
    private $collectionName;

    /**
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     */
    public function __construct(ManagerInterface $manager, $databaseName, $collectionName, array $options = [])
    {
        $this->manager = $manager;
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;

        $options += [
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ];

        $options = CollectionOptions::resolve($options);
        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
    }

    /**
     * @param array $pipeline
     * @param array $options
     * @return CursorInterface
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        if (array_key_exists('pipeline', $options)) {
            throw new InvalidArgumentException('Option "pipeline" is not allowed, use $pipeline argument.');
        }

        return $this->executeCommand(
            ['aggregate' => $this->collectionName],
            ['pipeline' => $pipeline] + $pipeline,
            AggregateResolver::class
        );
    }

    /**
     * @param WriteModelInterface[] $requests
     * @param array $options
     * @return WriteResult
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;
        unset($options['writeConcern']);

        $builder = new BulkWriteBuilder();
        $builder->addMany($requests);
        $bulk = $builder->getBulk($options);

        return $this->manager->executeBulkWrite($this->getNamespace(), $bulk, $writeConcern);
    }

    /**
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter = [], array $options = [])
    {
        if (array_key_exists('query', $options)) {
            throw new InvalidArgumentException('Option "query" is not allowed, use $filter argument.');
        }

        $cursor = $this->executeCommand(
            ['count' => $this->collectionName],
            ['query' => $filter] + $options,
            CountResolver::class
        );

        $cursor->setTypeMap(TypeMapOptions::getDefault());
        $result = $cursor->current();
        if (!isset($result['n'])) {
            throw new UnexpectedResultException('Command "count" did not return expected "n" field.');
        }

        return (int)$result['n'];
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
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes array cannot be empty');
        }

        if (array_key_exists('indexes', $options)) {
            throw new InvalidArgumentException(
                'Option "indexes" is not allowed, use $indexes argument'
            );
        }

        $compiledIndexes = array_map(function (Index $index) {
            return $index->toArray();
        }, $indexes);

        $options += ['indexes' => $compiledIndexes];

        $this->executeCommand(
            ['createIndexes' => $this->collectionName],
            $options,
            CreateIndexesResolver::class
        );

        return array_map(function(Index $index) {
            return $index->getName();
        }, $indexes);
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

    /**
     * @param array $filter
     * @param array $options
     * @return CursorInterface
     */
    public function find(array $filter = [], array $options = [])
    {
        $defaults = [
            'readPreference' => $this->readPreference,
            'readConcern' => $this->readConcern,
            'typeMap' => $this->typeMap,
        ];
        $options += $defaults;

        $query = new FindQuery($filter, $options);

        return $this->manager->executeQuery(
            $this->getNamespace(),
            $query,
            $query->getReadPreference()
        );
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
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
    public function getNamespace()
    {
        return $this->databaseName . '.' . $this->collectionName;
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
     * @return array
     */
    public function listIndexes()
    {
        $command = new ListIndexes($this->databaseName, $this->collectionName);
        $cursor = $this->executeCommand($command);

        return iterator_to_array($cursor);
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