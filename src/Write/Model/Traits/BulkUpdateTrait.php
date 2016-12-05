<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Server;
use Tequila\MongoDB\Write\Model\Update;

trait BulkUpdateTrait
{
    /**
     * @var Update
     */
    private $update;

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk, Server $server)
    {
        $this->update->writeToBulk($bulk, $server);
    }
}