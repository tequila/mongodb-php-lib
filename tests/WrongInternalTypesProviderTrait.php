<?php

namespace Tequila\MongoDB\Tests;

trait WrongInternalTypesProviderTrait
{
    public static function getInvalidStringArgs()
    {
        $args = [];
        foreach (self::getInvalidStringValues() as $value) {
            $args[] = [$value];
        }

        return $args;
    }

    public static function getInvalidStringValues()
    {
        return [true, 12, null, [], new \stdClass(), 3.5];
    }

    public function getInvalidIntegerValues()
    {
        return [true, 1.0, null, [], new \stdClass(), 'one'];
    }

    public function getInvalidBooleanValues()
    {
        return [1.0, null, [], new \stdClass(), 'one'];
    }
}
