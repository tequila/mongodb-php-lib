<?php

namespace Tequila\MongoDB\Command\Type;

use MongoDB\Driver\ReadPreference;

trait PrimaryReadPreferenceTrait
{
    /**
     * @var ReadPreference
     */
    private static $readPreference;

    public static function getDefaultReadPreference()
    {
        if (!self::$readPreference) {
            self::$readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        return self::$readPreference;
    }

    public static function supportsReadPreference(ReadPreference $readPreference)
    {
        return ReadPreference::RP_PRIMARY === $readPreference->getMode();
    }
}
