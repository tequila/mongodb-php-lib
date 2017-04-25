<?php

namespace Tequila\MongoDB;

use MongoDB\BSON\Unserializable;
use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\OptionsResolver\BulkWrite\UpdateDocumentResolver;
use Tequila\MongoDB\OptionsResolver\CollectionOptionsResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindOneAndDeleteResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindOneAndUpdateResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\OptionsResolver\QueryOptionsResolver;
use Tequila\MongoDB\Traits\CommandExecutorTrait;
use Tequila\MongoDB\Traits\ExecuteCommandTrait;
use Tequila\MongoDB\Write\Model\DeleteMany;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\InsertMany;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\Write\Result\DeleteResult;
use Tequila\MongoDB\Write\Result\InsertManyResult;
use Tequila\MongoDB\Write\Result\InsertOneResult;
use Tequila\MongoDB\Write\Result\UpdateResult;

class Collection
{
    use CommandExecutorTrait;
    use ExecuteCommandTrait;

    /**
     * @var Manager
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
     * @var array
     */
    private $typeMap;

    /**
     * @param Manager $manager
     * @param string  $databaseName
     * @param string  $collectionName
     * @param array   $options
     */
    public function __construct(Manager $manager, $databaseName, $collectionName, array $options = [])
    {
        if (!$databaseName) {
            throw new InvalidArgumentException('$databaseName cannot be empty.');
        }

        if (!$collectionName) {
            throw new InvalidArgumentException('$collectionName cannot be empty.');
        }

        $this->manager = $manager;
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;

        $this->resolveOptions($options);
    }

    /**
     * @param array $pipeline
     * @param array $options
     *
     * @return Cursor
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        if (array_key_exists('pipeline', $options)) {
            throw new InvalidArgumentException('Option "pipeline" is not allowed, use $pipeline argument.');
        }

        return $this->executeCommand(
            ['aggregate' => $this->collectionName],
            ['pipeline' => $pipeline] + $options
        );
    }

    /**
     * @param WriteModelInterface[]|\Traversable $requests
     * @param array                              $options
     *
     * @return WriteResult
     */
    public function bulkWrite($requests, array $options = [])
    {
        if (isset($options['writeConcern'])) {
            if (!$options['writeConcern'] instanceof WriteConcern) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Option "writeConcern" is expected to be "%s", "%s" given.',
                        WriteConcern::class,
                        \Tequila\MongoDB\getType($options['writeConcern'])
                    )
                );
            }

            $writeConcern = $options['writeConcern'];
            unset($options['writeConcern']);
        } else {
            $writeConcern = $this->writeConcern;
        }

        $bulkWrite = new BulkWrite($requests, $options);

        return $this->manager->executeBulkWrite($this->getNamespace(), $bulkWrite, $writeConcern);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return int
     */
    public function count(array $filter = [], array $options = [])
    {
        $cursor = $this->executeCommand(
            ['count' => $this->collectionName, 'query' => (object) $filter],
            $options
        );

        $result = $cursor->current();
        if (!isset($result['n'])) {
            throw new UnexpectedResultException('Command "count" did not return expected "n" field.');
        }

        return (int) $result['n'];
    }

    /**
     * @param array $key
     * @param array $options
     *
     * @return string
     */
    public function createIndex(array $key, array $options = [])
    {
        $commandOptions = [];
        if (isset($options['writeConcern'])) {
            $commandOptions['writeConcern'] = $options['writeConcern'];
            unset($options['writeConcern']);
        }
        $index = new Index($key, $options);

        return current($this->createIndexes([$index], $commandOptions));
    }

    /**
     * @param Index[] $indexes
     * @param array   $options
     *
     * @return \string[]
     */
    public function createIndexes(array $indexes, array $options = [])
    {
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes array cannot be empty.');
        }

        $compiledIndexes = array_map(function (Index $index) {
            return $index->toArray();
        }, $indexes);

        $this->executeCommand(
            ['createIndexes' => $this->collectionName, 'indexes' => $compiledIndexes],
            $options
        );

        return array_map(function (Index $index) {
            return $index->getName();
        }, $indexes);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return DeleteResult
     */
    public function deleteMany(array $filter, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new DeleteMany($filter, $options);
        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new DeleteResult($bulkWriteResult);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return DeleteResult
     */
    public function deleteOne(array $filter, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new DeleteOne($filter, $options);
        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new DeleteResult($bulkWriteResult);
    }

    /**
     * @param string $fieldName
     * @param array  $filter
     * @param array  $options
     *
     * @return array
     */
    public function distinct($fieldName, array $filter = [], array $options = [])
    {
        if (!is_string($fieldName)) {
            throw new InvalidArgumentException('$fieldName must be a string.');
        }

        if (!$fieldName) {
            throw new InvalidArgumentException('$fieldName cannot be empty.');
        }

        $command = ['distinct' => $this->collectionName, 'key' => $fieldName];
        if ($filter) {
            $command['query'] = (object) $filter;
        }
        $options['typeMap'] = [];

        $cursor = $this->executeCommand($command, $options);

        $result = $cursor->current();
        if (!isset($result->values)) {
            throw new UnexpectedResultException(
                'Command "distinct" did not return expected "values" array.'
            );
        }

        return $result->values;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function drop(array $options = [])
    {
        try {
            $cursor = $this->executeCommand(['drop' => $this->collectionName], $options);
        } catch (MongoDBRuntimeException $e) {
            if ('ns not found' === $e->getMessage()) {
                return ['ok' => 0, 'errmsg' => $e->getMessage()];
            }

            throw $e;
        }

        return $cursor->current();
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function dropIndexes(array $options = [])
    {
        $command = [
            'dropIndexes' => $this->collectionName,
            'index' => '*',
        ];
        $cursor = $this->executeCommand($command, $options);

        return $cursor->current();
    }

    /**
     * @param string $indexName
     * @param array  $options
     *
     * @return array
     */
    public function dropIndex($indexName, array $options = [])
    {
        if ('*' === $indexName) {
            throw new InvalidArgumentException(
                'Invalid $indexName "*", use dropIndexes() method to delete all indexes.'
            );
        }

        $command = [
            'dropIndexes' => $this->collectionName,
            'index' => $indexName,
        ];

        $cursor = $this->executeCommand($command, $options);

        return $cursor->current();
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return QueryCursor
     */
    public function find(array $filter = [], array $options = [])
    {
        $options += ['typeMap' => $this->typeMap];
        $options = QueryOptionsResolver::resolveStatic($options);
        $typeMap = $options['typeMap'];
        unset($options['typeMap']);

        if (isset($options['readPreference'])) {
            $readPreference = $options['readPreference'];
            unset($options['readPreference']);
        } else {
            $readPreference = $this->readPreference;
        }

        $query = new Query($filter, $options);
        $query->setDefaultReadConcern($this->readConcern);

        $cursor = $this->manager->executeQuery(
            $this->getNamespace(),
            $query,
            $readPreference
        );

        $cursor->setTypeMap($typeMap);

        return $cursor;
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return array|Unserializable|null
     */
    public function findOne(array $filter = [], array $options = [])
    {
        $cursor = $this->find($filter, ['limit' => 1] + $options);

        return ($document = $cursor->current()) ? $document : null;
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return array|Unserializable|null
     */
    public function findOneAndDelete(array $filter, array $options = [])
    {
        $options = ['remove' => true] + FindOneAndDeleteResolver::resolveStatic($options);

        return $this->findAndModify($filter, $options);
    }

    /**
     * @param array        $filter
     * @param array|object $replacement
     * @param array        $options
     *
     * @return array|Unserializable|null
     */
    public function findOneAndReplace(array $filter, $replacement, array $options = [])
    {
        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, "%s" given.',
                    \Tequila\MongoDB\getType($replacement)
                )
            );
        }

        try {
            ensureValidDocument($replacement);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid $replacement document: %s', $e->getMessage())
            );
        }

        $options = ['update' => (object) $replacement] + FindOneAndUpdateResolver::resolveStatic($options);

        return $this->findAndModify($filter, $options);
    }

    /**
     * @param array $filter
     * @param $update
     * @param array $options
     *
     * @return array|Unserializable|null
     */
    public function findOneAndUpdate(array $filter, array $update, array $options = [])
    {
        UpdateDocumentResolver::resolveStatic($update);

        $options = ['update' => (object) $update] + FindOneAndUpdateResolver::resolveStatic($options);

        return $this->findAndModify($filter, $options);
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
        return $this->databaseName.'.'.$this->collectionName;
    }

    /**
     * @param array|\Traversable $documents
     * @param array              $options
     *
     * @return InsertManyResult
     */
    public function insertMany($documents, array $options = [])
    {
        $writeModel = new InsertMany($documents);
        $bulkWriteResult = $this->bulkWrite([$writeModel], $options);

        return new InsertManyResult($bulkWriteResult);
    }

    /**
     * @param array|object $document
     * @param array        $options
     *
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
        $command = new SimpleCommand(['listIndexes' => $this->collectionName]);
        $cursor = $this->manager->executeCommand(
            $this->databaseName,
            $command,
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return iterator_to_array($cursor);
    }

    /**
     * @param array|object $filter
     * @param array|object $replacement
     * @param array        $options
     *
     * @return UpdateResult
     */
    public function replaceOne(array $filter, $replacement, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new ReplaceOne($filter, $replacement, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array        $filter
     * @param array|object $update
     * @param array        $options
     *
     * @return UpdateResult
     */
    public function updateMany(array $filter, $update, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new UpdateMany($filter, $update, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array        $filter
     * @param array|object $update
     * @param array        $options
     *
     * @return UpdateResult
     */
    public function updateOne(array $filter, $update, array $options = [])
    {
        list($bulkOptions, $options) = self::extractBulkWriteOptions($options);
        $model = new UpdateOne($filter, $update, $options);

        $bulkWriteResult = $this->bulkWrite([$model], $bulkOptions);

        return new UpdateResult($bulkWriteResult);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private static function extractBulkWriteOptions(array $options)
    {
        $definedBulkOptions = ['bypassDocumentValidation', 'ordered', 'writeConcern'];
        $bulkWriteOptions = array_intersect_key($options, array_flip($definedBulkOptions));
        $operationOptions = array_diff_key($options, $bulkWriteOptions);

        return [$bulkWriteOptions, $operationOptions];
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return array|Unserializable|object|null
     */
    private function findAndModify(array $filter, array $options)
    {
        $command = [
            'findAndModify' => $this->collectionName,
            'query' => (object) $filter,
        ];

        $options += ['typeMap' => $this->typeMap];
        $typeMap = $options['typeMap'];
        unset($options['typeMap']);

        $cursor = $this->executeCommand($command, $options);
        $result = $cursor->current();

        if (!array_key_exists('value', $result)) {
            throw new UnexpectedResultException(
                'Command "findAndModify" did not return expected "value" document.'
            );
        }

        $result = $result['value'];

        return \Tequila\MongoDB\applyTypeMap($result, $typeMap);
    }

    /**
     * @param array $options
     */
    private function resolveOptions(array $options)
    {
        $options += [
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
            'typeMap' => [],
        ];

        $options = CollectionOptionsResolver::resolveStatic($options);

        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
        $this->typeMap = $options['typeMap'];
    }
}
