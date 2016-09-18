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
    public static function ensureArrayRecursive($value)
    {
        if ($value instanceof Serializable) {
            $value = $value->bsonSerialize();
        }

        $value = (array) $value;

        foreach ($value as $key => $nestedValue) {
            if (is_array($nestedValue) || is_object($nestedValue)) {
                $value[$key] = self::ensureArrayRecursive($value);
            }
        }

        return $value;
    }

    /**
     * Checks if array is a list
     *
     * @param array $array
     * @return bool
     */
    public static function isList(array $array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function getType($value) {
        return is_object($value) ? get_class($value) : \gettype($value);
    }
}