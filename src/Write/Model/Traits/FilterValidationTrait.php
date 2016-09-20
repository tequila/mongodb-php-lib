<?php

namespace Tequilla\MongoDB\Write\Model\Traits;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\TypeUtils;

trait FilterValidationTrait
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