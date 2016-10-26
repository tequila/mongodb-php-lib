<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\ServerInfo;

trait EnsureWriteConcernOptionSupported
{
    private function ensureWriteConcernOptionSupported(ServerInfo $serverInfo)
    {
        $wireVersionForWriteConcern = 5;

        if (!$serverInfo->supportsFeature($wireVersionForWriteConcern)) {
            throw new UnsupportedException(
                'Option "writeConcern" is not supported by the server'
            );
        }
    }
}