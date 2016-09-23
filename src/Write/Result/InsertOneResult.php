<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Write\Bulk\BulkWriteResult;

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