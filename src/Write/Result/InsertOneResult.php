<?php

namespace Tequilla\MongoDB\Write\Result;

use Tequilla\MongoDB\Exception\UnexpectedResultException;
use Tequilla\MongoDB\Write\Bulk\BulkWriteResult;

class InsertOneResult
{
    use Traits\BulkWriteResultAwareTrait;

    public function getInsertedId()
    {
        foreach ($this->bulkWriteResult->getInsertedIds() as $id) {
            return $id;
        }

        throw new UnexpectedResultException(
            sprintf(
                '%s::getInsertedIds() returned empty array, though there was insert operation',
                BulkWriteResult::class
            )
        );
    }
}