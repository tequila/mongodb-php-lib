<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Write\Result\WriteResultDecoratorTrait;

class UpdateResult
{
    use Tequila\MongoDB\Write\Result\WriteResultDecoratorTrait;
    /**
     * @return int
     */
    public function getMatchedCount()
    {
        return $this->writeResult->getMatchedCount();
    }

    /**
     * @return int
     */
    public function getModifiedCount()
    {
        return $this->writeResult->getModifiedCount();
    }

    /**
     * @return int
     */
    public function getUpsertedCount()
    {
        return $this->writeResult->getUpsertedCount();
    }

    /**
     * @return \MongoDB\BSON\ObjectID|null
     */
    public function getUpsertedId()
    {
        foreach ($this->writeResult->getUpsertedIds() as $id) {
            return $id;
        }

        return null;
    }
}