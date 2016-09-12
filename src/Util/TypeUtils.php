<?php

namespace Tequilla\MongoDB\Util;

use MongoDB\BSON\Serializable;
use Tequilla\MongoDB\Exception\InvalidArgumentException;

class TypeUtils
{
    /**
     * @param mixed $value
     * @return string
     */
    public static function getType($value) {
        return is_object($value) ? get_class($value) : \gettype($value);
    }

    public static function convertToArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_object($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Value must be an array or an object, %s given',
                    self::getType($value)
                )
            );
        }

        if ($value instanceof Serializable) {
            $value = $value->bsonSerialize();
        }

        return (array) $value;
    }
}