<?php

namespace Tequila\MongoDB\Write\Result;

class InsertManyResult
{
    use Traits\BulkWriteResultAwareTrait;

    /**
     * @return int
     */
    public function getInsertedCount()
    {
        return $this->bulkWriteResult->getInsertedCount();
    }

    /**
     * @return \MongoDB\BSON\ObjectID[]|array
     */
    public function getInsertedIds()
    {
        return $this->bulkWriteResult->getInsertedIds();
    }
}