<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Bulk\BulkWrite;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     * @return mixed
     */
    public function writeToBulk(BulkWrite $bulk);
}