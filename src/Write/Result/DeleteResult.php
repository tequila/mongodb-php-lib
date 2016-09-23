<?php

namespace Tequila\MongoDB\Write\Result;

class DeleteResult
{
    use Traits\BulkWriteResultAwareTrait;

    public function getDeletedCount()
    {
        return $this->bulkWriteResult->getDeletedCount();
    }
}