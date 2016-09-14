<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\BulkWrite\BulkWrite;

trait BulkDeleteTrait
{
    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->delete($this->filter, $this->options);
    }
}