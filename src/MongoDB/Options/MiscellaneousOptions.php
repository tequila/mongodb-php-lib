<?php

namespace Tequilla\MongoDB\Options;

class MiscellaneousOptions implements OptionsInterface
{
    const UUID_REPRESENTATION = 'uuidRepresentation';
    
    public static function getAll()
    {
        return [
            self::UUID_REPRESENTATION,
        ];
    }
}