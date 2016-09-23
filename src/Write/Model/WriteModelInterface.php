<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Write\Bulk\BulkWrite;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     * @return mixed
     */
    public function writeToBulk(BulkWrite $bulk);
}