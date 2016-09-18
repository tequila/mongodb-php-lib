<?php

namespace Tequilla\MongoDB\Write\Bulk;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Serializable;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Exception\Exception as MongoDBException;
use MongoDB\Driver\WriteConcern;
use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Exception\RuntimeException;
use Tequilla\MongoDB\Util\TypeUtils;
use Tequilla\MongoDB\Write\Model\WriteModelInterface;

class BulkWrite
{
    /**
     * @var \Tequilla\MongoDB\Write\Model\WriteModelInterface[]
     */
    private $requests;

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
     * @param \Tequilla\MongoDB\Write\Model\WriteModelInterface[] $requests
     * @param array $options
     */
    public function __construct(array $requests, array $options)
    {
        self::validateRequests($requests);
        $this->requests = $requests;
        $this->options = BulkWriteOptions::getCachedResolver()->resolve($options);

        if (isset($this->options['writeConcern'])) {
            $this->writeConcern = $this->options['writeConcern'];
            unset($this->options['writeConcern']);
        }

        $this->bulk = new Bulk($this->options);
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
            $id = self::extractIdFromDocument($document);
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
     * @param Connection $connection
     * @param string $databaseName
     * @param string $collectionName
     * @return BulkWriteResult
     */
    public function execute(Connection $connection, $databaseName, $collectionName)
    {
        foreach ($this->requests as $i => $request) {
            try {
                $request->writeToBulk($this);
            } catch(MongoDBException $e) {
                throw new RuntimeException(
                    sprintf (
                        'Exception "%s" during adding $requests[%s] to BulkWrite',
                        get_class($e),
                        $i
                    )
                );
            }
        }

        $writeResult = $connection->executeBulkWrite(
            $databaseName,
            $collectionName,
            $this->bulk,
            $this->writeConcern
        );

        return new BulkWriteResult($writeResult, $this->insertedIds);
    }

    private static function validateRequests(array $requests)
    {
        if (!TypeUtils::isList($requests)) {
            throw new InvalidArgumentException('$requests is not a list');
        }

        foreach ($requests as $i => $request) {
            if (!$request instanceof WriteModelInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Every request in $requests must be an instance of %s, %s given in $requests[%d]',
                        WriteModelInterface::class,
                        TypeUtils::getType($request),
                        $i
                    )
                );
            }
        }
    }

    /**
     * @param array|object $document
     * @return ObjectID
     */
    private static function extractIdFromDocument($document)
    {
        if ($document instanceof Serializable) {
            return self::extractIdFromDocument($document->bsonSerialize());
        }

        return is_array($document) ? $document['_id'] : $document->_id;
    }
}