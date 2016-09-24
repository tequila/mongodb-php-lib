<?php

namespace Tequila\MongoDB\Util;

final class TypeUtil
{
    /**
     * @param mixed $value
     * @return string
     */
    public static function getType($value) {
        return is_object($value) ? get_class($value) : \gettype($value);
    }
}