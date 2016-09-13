<?php

namespace Tequilla\MongoDB\Util;

use MongoDB\BSON\Serializable;
use Tequilla\MongoDB\Exception\InvalidArgumentException;

final class TypeUtils
{
    /**
     * @param string $className
     * @param string $parentName
     */
    public static function ensureIsSubclassOf($className, $parentName) {
        self::ensureClassExists($className);

        if (!is_subclass_of($className, $parentName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Only classes, which implement "%s" are allowed, %s given',
                    $parentName,
                    $className
                )
            );
        }
    }

    /**
     * @param string $className
     */
    public static function ensureClassExists($className) {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                sprintf('Class name must be a string, %s given', self::getType($className))
            );
        }

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" is not found', $className)
            );
        }
    }

    /**
     * @param $value
     * @return array
     */
    public static function ensureArray($value)
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

    /**
     * @param mixed $value
     * @return string
     */
    public static function getType($value) {
        return is_object($value) ? get_class($value) : \gettype($value);
    }
}