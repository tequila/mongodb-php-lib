<?php

namespace Tequilla\MongoDB\Write\Model\Traits;

use Tequilla\MongoDB\Write\Bulk\BulkWrite;

trait BulkUpdateTrait
{
    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array|object
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * @see \Tequilla\MongoDB\WriteModel\WriteModelInterface::writeToBulk()
     *
     * @param \Tequilla\MongoDB\Write\Bulk\BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}