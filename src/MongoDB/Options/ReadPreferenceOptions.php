<?php

namespace Tequilla\MongoDB\Options;

final class ReadPreferenceOptions implements OptionsInterface
{
    const READ_PREFERENCE = 'readPreference';
    const READ_PREFERENCE_TAGS = 'readPreferenceTags';

    public static function getAll()
    {
        return [
            self::READ_PREFERENCE,
            self::READ_PREFERENCE_TAGS,
        ];
    }
}