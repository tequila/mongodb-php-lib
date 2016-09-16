<?php

namespace Tequilla\MongoDB\BulkWrite;

use MongoDB\Driver\WriteConcernError;
use MongoDB\Driver\WriteError;
use MongoDB\Driver\WriteResult;
use Tequilla\MongoDB\Exception\BadMethodCallException;

class BulkWriteResult
{
    /**
     * @var WriteResult
     */
    private $writeResult;

    /**
     * @var array
     */
    private $insertedIds;

    /**
     * @var bool
     */
    private $isAcknowledged;

    /**
     * @param WriteResult $writeResult
     * @param array $insertedIds
     */
    public function __construct(WriteResult $writeResult, array $insertedIds = [])
    {
        $this->writeResult = $writeResult;
        $this->insertedIds = $insertedIds;
        $this->isAcknowledged = $writeResult->isAcknowledged();
    }

    /**
     * @return array
     */
    public function getInsertedIds()
    {
        return $this->insertedIds;
    }

    /**
     * @return int
     */
    public function getDeletedCount()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getDeletedCount();
    }

    /**
     * @return int
     */
    public function getInsertedCount()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getInsertedCount();
    }

    /**
     * @return int
     */
    public function getMatchedCount()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getMatchedCount();
    }

    /**
     * @return int
     */
    public function getModifiedCount()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getModifiedCount();
    }

    /**
     * @return int
     */
    public function getUpsertedCount()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getUpsertedCount();
    }

    /**
     * @return \MongoDB\BSON\ObjectID
     */
    public function getUpsertedIds()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getUpsertedIds();
    }

    /**
     * @return WriteConcernError
     */
    public function getWriteConcernError()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getWriteConcernError();
    }

    /**
     * @return WriteError[]
     */
    public function getWriteErrors()
    {
        $this->ensureAcknowledgedWriteResult(__METHOD__);

        return $this->writeResult->getWriteErrors();
    }

    /**
     * @return \MongoDB\Driver\Server
     */
    public function getServer()
    {
        return $this->writeResult->getServer();
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->isAcknowledged;
    }

    private function ensureAcknowledgedWriteResult($method)
    {
        if (!$this->isAcknowledged) {
            throw new BadMethodCallException(
                sprintf(
                    'Method %s cannot be called on unacknowledged write result',
                    $method
                )
            );
        }
    }
}