<?php

namespace Tequila\MongoDB\Tests\Traits;

use Tequila\MongoDB\ServerInfo;

trait ServerInfoTrait
{
    /**
     * @return ServerInfo
     */
    private function getServerInfo()
    {
        return $this->prophesize(ServerInfo::class)->reveal();
    }
}