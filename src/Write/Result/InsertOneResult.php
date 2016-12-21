<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\WriteResult;

class InsertOneResult
{
    use WriteResultDecoratorTrait;

    public function getInsertedId()
    {
        foreach ($this->writeResult->getInsertedIds() as $id) {
            return $id;
        }

        throw new UnexpectedResultException(
            sprintf(
                '%s::getInsertedIds() returned empty array, though there was an insert operation.',
                WriteResult::class
            )
        );
    }
}