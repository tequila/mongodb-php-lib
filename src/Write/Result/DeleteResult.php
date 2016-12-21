<?php

namespace Tequila\MongoDB\Write\Result;

class DeleteResult
{
    use WriteResultDecoratorTrait;

    public function getDeletedCount()
    {
        return $this->writeResult->getDeletedCount();
    }
}