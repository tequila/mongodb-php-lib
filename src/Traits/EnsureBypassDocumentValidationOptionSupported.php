<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\ServerInfo;

trait EnsureBypassDocumentValidationOptionSupported
{
    private function ensureBypassDocumentValidationOptionSupported(ServerInfo $serverInfo)
    {
        $wireVersion = 4;

        if (!$serverInfo->supportsFeature($wireVersion)) {
            throw new UnsupportedException(
                'Option "bypassDocumentValidation" is not supported by the server'
            );
        }
    }
}