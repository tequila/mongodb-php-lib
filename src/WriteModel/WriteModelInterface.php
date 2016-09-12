<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;

interface WriteModelInterface
{
    public function writeToBulk(BulkWrite $bulk);
}