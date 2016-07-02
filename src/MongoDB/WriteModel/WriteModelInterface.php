<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;

interface WriteModelInterface
{
    public function addToBulk(BulkWrite $bulk);
}