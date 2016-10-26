<?php

namespace Tequila\MongoDB\Write\Result;

use Tequila\MongoDB\Traits\WriteResultDecoratorTrait;

class DeleteResult
{
    use WriteResultDecoratorTrait;

    public function getDeletedCount()
    {
        return $this->writeResult->getDeletedCount();
    }
}