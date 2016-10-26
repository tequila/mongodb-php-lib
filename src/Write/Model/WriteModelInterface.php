<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk);
}