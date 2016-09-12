<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\StringUtils;
use function Tequilla\MongoDB\getType;

trait ValidateFilterTrait
{
    private static function validateFilter($filter)
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$filter must be an array or an object, %s given',
                    getType($filter)
                )
            );
        }

        $filter = (array) $filter;
        foreach ($filter as $fieldName => $conditions) {
            if (StringUtils::startsWith($fieldName, '$')) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Field names cannot start with "$" character, field name "%s" given',
                        $fieldName
                    )
                );
            }
        }
    }
}