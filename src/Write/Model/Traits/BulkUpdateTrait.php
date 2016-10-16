<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\Write\Bulk\BulkWrite;

trait BulkUpdateTrait
{
    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * @see \Tequila\MongoDB\Write\Model\WriteModelInterface::writeToBulk()
     *
     * @param \Tequila\MongoDB\Write\Bulk\BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}