<?php

namespace Tequila\MongoDB\Write\Bulk;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Serializable;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\Util\TypeUtil;
use Tequila\MongoDB\Write\Model\WriteModelInterface;

class BulkWrite
{
    /**
     * @var \Tequila\MongoDB\Write\Model\WriteModelInterface[]
     */
    private $writeModels;

    /**
     * @var array
     */
    private $options;

    /**
     * @var Bulk
     */
    private $bulk;

    /**
     * @var array
     */
    private $insertedIds = [];

    /**
     * @var WriteConcern
     */
    private $writeConcern;

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

        $this->bulk = new Bulk($this->options);
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
        $index = $this->bulk->count();
        $id = $this->bulk->insert($document);
        if (null === $id) {
            $id = $this->extractIdFromDocument($document);
        }

        $this->insertedIds[$index] = $id;

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
        $this->bulk->delete($filter, $options);
    }

    /**
     * Wraps @see \MongoDB\Driver\BulkWrite::count()
     *
     * @return int
     */
    public function count()
    {
        return $this->bulk->count();
    }

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @return BulkWriteResult
     */
    public function execute(Manager $manager, $databaseName, $collectionName)
    {
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
}