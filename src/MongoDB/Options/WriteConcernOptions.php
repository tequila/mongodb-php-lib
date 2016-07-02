<?php

namespace Tequilla\MongoDB\Options;

class WriteConcernOptions implements OptionsInterface
{
    const WRITE_CONCERN = 'w';
    const WRITE_CONCERN_TIMEOUT_MS = 'wtimeoutMS';
    const JOURNAL = 'journal';
    
    public static function getAll()
    {
        return [
            self::WRITE_CONCERN,
            self::WRITE_CONCERN_TIMEOUT_MS,
            self::JOURNAL,
        ];
    }
}