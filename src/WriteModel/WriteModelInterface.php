<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\BulkWrite\BulkWrite;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     * @return mixed
     */
    public function writeToBulk(BulkWrite $bulk);
}