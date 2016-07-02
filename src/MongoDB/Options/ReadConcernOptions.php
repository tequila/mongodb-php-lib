<?php

namespace Tequilla\MongoDB\Options;

class ReadConcernOptions implements OptionsInterface
{
    const READ_CONCERN_LEVEL = 'readConcernLevel';
    
    public static function getAll()
    {
        return [
            self::READ_CONCERN_LEVEL,
        ];
    }
}