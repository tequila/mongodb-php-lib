<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\ServerInfo;

trait EnsureReadConcernOptionSupported
{
    private function ensureReadConcernOptionSupported(ServerInfo $serverInfo)
    {
        $wireVersion = 4;

        if (!$serverInfo->supportsFeature($wireVersion)) {
            throw new UnsupportedException(
                'Option "readConcern" is not supported by the server'
            );
        }
    }
}