<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Server;
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
     * @param Server $server
     */
    public function writeToBulk(BulkWrite $bulk, Server $server)
    {
        $this->delete->writeToBulk($bulk, $server);
    }
}