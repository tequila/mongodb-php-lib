<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Traits\WriteResultDecoratorTrait;

class InsertManyResult
{
    use WriteResultDecoratorTrait;

    /**
     * @return int
     */
    public function getInsertedCount()
    {
        return $this->writeResult->getInsertedCount();
    }

    /**
     * @return \MongoDB\BSON\ObjectID[]|array
     */
    public function getInsertedIds()
    {
        return $this->writeResult->getInsertedIds();
    }
}