<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\Server;

trait CheckCompatibilityTrait
{
    private function checkCompatibility(array $options, Server $server)
    {
        if (isset($options['collation']) && !$server->supportsCollation()) {
            throw new UnsupportedException(
                'Option "collation" is not supported by the server.'
            );
        }
    }
}