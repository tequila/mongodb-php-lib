<?php

namespace Tequilla\MongoDB\Write\Model\Traits;

use Tequilla\MongoDB\Write\Bulk\BulkWrite;

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