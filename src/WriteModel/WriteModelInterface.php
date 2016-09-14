<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     * @return mixed
     */
    public function writeToBulk(BulkWrite $bulk);
}