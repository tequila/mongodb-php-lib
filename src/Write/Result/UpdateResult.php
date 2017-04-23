<?php

namespace Tequila\MongoDB\Write\Result;

class UpdateResult
{
    use WriteResultDecoratorTrait;

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
        $upsertedIds = $this->writeResult->getUpsertedIds();

        return array_shift($upsertedIds);
    }
}
