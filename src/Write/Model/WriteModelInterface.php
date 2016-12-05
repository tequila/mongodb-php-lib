<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Server;

interface WriteModelInterface
{
    /**
     * @param BulkWrite $bulk
     * @param Server $server
     * @return
     * @void
     */
    public function writeToBulk(BulkWrite $bulk, Server $server);
}