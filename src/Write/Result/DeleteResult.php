<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Write\Result\WriteResultDecoratorTrait;

class DeleteResult
{
    use Tequila\MongoDB\Write\Result\WriteResultDecoratorTrait;

    public function getDeletedCount()
    {
        return $this->writeResult->getDeletedCount();
    }
}