<?php

namespace Tequila\MongoDB\Write\Result;

class DeleteResult
{
    use WriteResultDecoratorTrait;

    /**
     * @return int
     */
    public function getDeletedCount()
    {
        return $this->writeResult->getDeletedCount();
    }
}
