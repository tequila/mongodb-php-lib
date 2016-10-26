<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\ServerInfo;

trait EnsureCollationOptionSupportedTrait
{
    private function ensureCollationOptionSupported(ServerInfo $serverInfo)
    {
        $wireVersion = 5;

        if (!$serverInfo->supportsFeature($wireVersion)) {
            throw new UnsupportedException('Option "collation" is not supported by the server');
        }
    }
}