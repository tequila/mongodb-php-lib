<?php

namespace WriteModel;

use MongoDB\Driver\BulkWrite;

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
     * @param BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}