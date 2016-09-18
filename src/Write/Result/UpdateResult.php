<?php

namespace Tequilla\MongoDB\Write\Result;

class UpdateResult
{
    use Traits\BUlkWriteResultAwareTrait;
    /**
     * @return int
     */
    public function getMatchedCount()
    {
        return $this->bulkWriteResult->getMatchedCount();
    }

    /**
     * @return int
     */
    public function getModifiedCount()
    {
        return $this->bulkWriteResult->getModifiedCount();
    }

    /**
     * @return int
     */
    public function getUpsertedCount()
    {
        return $this->bulkWriteResult->getUpsertedCount();
    }

    /**
     * @return \MongoDB\BSON\ObjectID|null
     */
    public function getUpsertedId()
    {
        foreach ($this->bulkWriteResult->getUpsertedIds() as $id) {
            return $id;
        }

        return null;
    }
}