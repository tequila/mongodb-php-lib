<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\TypeUtils;

trait ValidateFilterTrait
{
    private static function validateFilter($filter)
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