<?php

namespace Tequila\MongoDB\Write\Bulk;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Serializable;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\BadMethodCallException;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\Util\TypeUtil;
use Tequila\MongoDB\Write\Model\WriteModelInterface;

class BulkWrite
{
    /**
     * @var Bulk
     */
    private $bulk;

    /**
     * @var integer
     */
    private $currentOperationIndex;

    /**
     * @var bool
     */
    private $executionInProgress = false;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $insertedIds = [];

    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * @var \Tequila\MongoDB\Write\Model\WriteModelInterface[]
     */
    private $writeModels;

    /**
     * @param \Tequila\MongoDB\Write\Model\WriteModelInterface[] $writeModels
     * @param array $options
     */
    public function __construct(array $writeModels, array $options = [])
    {
        $this->writeModels = $writeModels;
        $this->options = BulkWriteOptions::resolve($options);

        if (isset($this->options['writeConcern'])) {
            $this->writeConcern = $this->options['writeConcern'];
            unset($this->options['writeConcern']);
        }
    }

    /**
     * Adds write model to bulk
     *
     * @param WriteModelInterface $writeModel
     */
    public function add(WriteModelInterface $writeModel)
    {
        $this->writeModels[] = $writeModel;
    }

    /**
     * Wraps @see \MongoDB\Driver\BulkWrite::insert() to save inserted id and always return it
     *
     * @param array|object $document
     * @return ObjectID
     */
    public function insert($document)
    {
        $this->ensureMethodCallIsAllowed(__METHOD__);

        $id = $this->bulk->insert($document);
        if (null === $id) {
            $id = $this->extractIdFromDocument($document);
        }

        $this->insertedIds[$this->currentOperationIndex] = $id;

        return $id;
    }

    /**
     * Wraps @see \MongoDB\Driver\BulkWrite::update()
     *
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function update($filter, $update, array $options = [])
    {
        $this->ensureMethodCallIsAllowed(__METHOD__);

        $this->bulk->update($filter, $update, $options);
    }

    /**
     * Wraps @see \MongoDB\Driver\BulkWrite::delete()
     *
     * @param array|object $filter
     * @param array $options
     */
    public function delete($filter, array $options = [])
    {
        $this->ensureMethodCallIsAllowed(__METHOD__);

        $this->bulk->delete($filter, $options);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->writeModels);
    }

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @return BulkWriteResult
     */
    public function execute(Manager $manager, $databaseName, $collectionName)
    {
        $this->executionInProgress = true;
        $this->bulk = new Bulk($this->options);

        $expectedIndex = 0;
        foreach ($this->writeModels as $i => $model) {
            if ($i !== $expectedIndex) {
                throw new InvalidArgumentException(
                    sprintf(
                        '$writeModels is not a list. Expected index "%d", index "%s" given',
                        $expectedIndex,
                        $i
                    )
                );
            }

            if (!$model instanceof WriteModelInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        '$writeModels[%d] must be an instance of %s, %s given',
                        $i,
                        WriteModelInterface::class,
                        TypeUtil::getType($model)
                    )
                );
            }

            $this->currentOperationIndex = $i;

            $model->writeToBulk($this);
            $expectedIndex += 1;
        }

        if (0 === $this->bulk->count()) {
            throw new LogicException('Attempt to execute empty bulk');
        }

        $writeResult = $manager->executeBulkWrite(
            $databaseName . '.' . $collectionName,
            $this->bulk,
            $this->writeConcern
        );

        $this->executionInProgress = false;

        return new BulkWriteResult($writeResult, $this->insertedIds);
    }

    /**
     * @param array|object $document
     * @return ObjectID
     */
    private function extractIdFromDocument($document)
    {
        if ($document instanceof Serializable) {
            return self::extractIdFromDocument($document->bsonSerialize());
        }

        return is_array($document) ? $document['_id'] : $document->_id;
    }

    private function ensureMethodCallIsAllowed($method)
    {
        if (true !== $this->executionInProgress) {
            throw new BadMethodCallException(
                sprintf(
                    'Method %s can only be called from %s instance during the process of bulk execution',
                    $method,
                    WriteModelInterface::class
                )
            );
        }
    }
}