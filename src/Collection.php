<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\OptionsResolver\Command\AggregateResolver;
use Tequila\MongoDB\OptionsResolver\Command\CountResolver;
use Tequila\MongoDB\OptionsResolver\Command\CreateIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\DistinctResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindAndModifyResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindOneAndDeleteResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindOneAndUpdateResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\OptionsResolver\BulkWrite\BulkWriteResolver;
use Tequila\MongoDB\OptionsResolver\DatabaseOptionsResolver;
use Tequila\MongoDB\OptionsResolver\QueryOptionsResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;
use Tequila\MongoDB\Traits\CommandBuilderTrait;
use Tequila\MongoDB\Traits\ExecuteCommandTrait;
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
    use ExecuteCommandTrait;

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

        $options = ResolverFactory::get(DatabaseOptionsResolver::class)->resolve($options);
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
            ['pipeline' => $pipeline] + $options,
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
        if (isset($options['writeConcern'])) {
            if (!$options['writeConcern'] instanceof WriteConcern) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Option "writeConcern" must be an instance of "%s", but is of type "%s".',
                        WriteConcern::class,
                        getType($options['writeConcern'])
                    )
                );
            }

            $writeConcern = $options['writeConcern'];
            unset($options['writeConcern']);
        } else {
            $writeConcern = $this->writeConcern;
        }

        $compiler = new BulkCompiler($options);
        $compiler->add($requests);

        return $this->manager->executeBulkWrite($this->getNamespace(), $compiler, $writeConcern);
    }

    /**
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter = [], array $options = [])
    {
        $cursor = $this->executeCommand(
            ['count' => $this->collectionName, 'query' => (object)$filter],
            $options,
            CountResolver::class
        );

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
            throw new InvalidArgumentException('$indexes array cannot be empty.');
        }

        $compiledIndexes = array_map(function (Index $index) {
            return $index->toArray();
        }, $indexes);

        $this->executeCommand(
            ['createIndexes' => $this->collectionName, 'indexes' => $compiledIndexes],
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
     * @param $fieldName
     * @param array $filter
     * @param array $options
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
            $command['query'] = (object)$filter;
        }

        $cursor = $this->executeCommand(
            $command,
            $options,
            DistinctResolver::class
        );

        $result = $cursor->current();
        if (!isset($result['values'])) {
            throw new UnexpectedResultException(
                'Command "distinct" did not return expected "values" array.'
            );
        }

        return $result['values'];
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $cursor = $this->executeCommand(
            ['drop' => $this->collectionName],
            $options,
            DropCollectionResolver::class
        );

        return $cursor->current();
    }

    /**
     * @param array $options
     * @return array
     */
    public function dropIndexes(array $options = [])
    {
        $command = [
            'dropIndexes' => $this->collectionName,
            'index' => '*',
        ];
        $cursor = $this->executeCommand($command, $options, DropIndexesResolver::class);

        return $cursor->current();
    }

    /**
     * @param string $indexName
     * @param array $options
     * @return array
     */
    public function dropIndex($indexName, array $options = [])
    {
        $command = [
            'dropIndexes' => $this->collectionName,
            'index' => $indexName,
        ];

        $cursor = $this->executeCommand($command, $options, DropIndexesResolver::class);

        return $cursor->current();
    }

    /**
     * @param array $filter
     * @param array $options
     * @return CursorInterface
     */
    public function find(array $filter = [], array $options = [])
    {
        $options = ResolverFactory::get(QueryOptionsResolver::class)->resolve($options);

        if (isset($options['readPreference'])) {
            $readPreference = $options['readPreference'];
            unset($options['readPreference']);
        } else {
            $readPreference = $this->readPreference;
        }

        $query = new Query($filter, $options);
        $query->setDefaultReadConcern($this->readConcern);

        return $this->manager->executeQuery(
            $this->getNamespace(),
            $query,
            $readPreference
        );
    }

    /**
     * @param array $filter
     * @param array $options
     * @return CursorInterface
     */
    public function findOneAndDelete(array $filter, array $options = [])
    {
        $command = [
            'findAndModify' => $this->collectionName,
            'query' => (object)$filter,
        ];

        $options = ['remove' => true] + ResolverFactory::get(FindOneAndDeleteResolver::class)->resolve($options);

        return $this->executeCommand($command, $options, FindAndModifyResolver::class);
    }

    /**
     * @param array $filter
     * @param array|object $replacement
     * @param array $options
     * @return CursorInterface
     */
    public function findOneAndReplace(array $filter, $replacement, array $options = [])
    {
        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, "%s" given.',
                    getType($replacement)
                )
            );
        }

        try {
            ensureValidDocument($replacement);
        } catch(InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid $replacement document: %s', $e->getMessage())
            );
        }

        return $this->findOneAndUpdate($filter, $replacement, $options);
    }

    /**
     * @param array $filter
     * @param $update
     * @param array $options
     * @return CursorInterface
     */
    public function findOneAndUpdate(array $filter, $update, array $options = [])
    {
        $command = [
            'findAndModify' => $this->collectionName,
            'query' => (object)$filter,
        ];

        $options = ResolverFactory::get(FindOneAndUpdateResolver::class)->resolve($options);
        $options = ['update' => (object)$update] + $options;

        return $this->executeCommand($command, $options, FindAndModifyResolver::class);
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
        $command = new SimpleCommand(['listIndexes' => $this->collectionName]);
        $cursor = $this->manager->executeCommand(
            $this->databaseName,
            $command,
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );

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
        $definedOptions = ResolverFactory::get(BulkWriteResolver::class)->getDefinedOptions();
        array_push($definedOptions, 'writeConcern');

        $bulkWriteOptions = array_intersect_key($options, array_flip($definedOptions));
        $operationOptions = array_diff_key($options, $bulkWriteOptions);

        return [$bulkWriteOptions, $operationOptions];
    }
}