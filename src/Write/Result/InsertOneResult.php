<?php

namespace Tequila\MongoDB\Write\Result;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\WriteResult;

class InsertOneResult
{
    use WriteResultDecoratorTrait;

    /**
     * @return ObjectID
     */
    public function getInsertedId()
    {
        $insertedIds = $this->writeResult->getInsertedIds();

        if (0 === count($insertedIds)) {
            throw new UnexpectedResultException(
                sprintf(
                    '%s::getInsertedIds() returned empty array, though there was an insert operation.',
                    WriteResult::class
                )
            );
        }

        return reset($insertedIds);
    }
}
