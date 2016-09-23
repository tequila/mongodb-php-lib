<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\TypeUtils;

trait EnsureValidFilterTrait
{
    public function ensureValidFilter($filter)
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$filter must be an array or an object, %s given',
                    TypeUtils::getType($filter)
                )
            );
        }
    }
}