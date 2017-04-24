<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Write\Model\Delete;

trait BulkDeleteTrait
{
    /**
     * @var Delete
     */
    private $delete;

    /**
     * @see \Tequila\MongoDB\Write\Model\WriteModelInterface::writeToBulk()
     *
     * @param BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $this->delete->writeToBulk($bulk);
    }
}
