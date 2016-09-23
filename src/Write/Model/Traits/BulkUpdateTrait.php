<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\Write\Bulk\BulkWrite;

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
     * @see \Tequila\MongoDB\WriteModel\WriteModelInterface::writeToBulk()
     *
     * @param \Tequila\MongoDB\Write\Bulk\BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}