<?php

namespace Tequila\MongoDB\Write\Result\Traits;

use Tequila\MongoDB\Write\Bulk\BulkWriteResult;

trait BulkWriteResultAwareTrait
{
    private $bulkWriteResult;

    /**
     * @param BulkWriteResult $bulkWriteResult
     */
    public function __construct(BulkWriteResult $bulkWriteResult)
    {
        $this->bulkWriteResult = $bulkWriteResult;
    }

    /**
     * @return bool
     */
    public function isAcknowledged()
    {
        return $this->bulkWriteResult->isAcknowledged();
    }

    /**
     * @return \MongoDB\Driver\WriteError[]
     */
    public function getWriteErrors()
    {
        return $this->bulkWriteResult->getWriteErrors();
    }

    /**
     * @return \MongoDB\Driver\WriteConcernError|null
     */
    public function getWriteConcernError()
    {
        return $this->bulkWriteResult->getWriteConcernError();
    }
}