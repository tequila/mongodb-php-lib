<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class ListDatabases implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return ['listDatabases' => 1];
    }
}