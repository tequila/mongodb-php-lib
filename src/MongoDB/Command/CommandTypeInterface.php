<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\ReadPreference;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

/**
 * Interface CommandInterface
 * @package Tequilla\MongoDB\Command
 */
interface CommandTypeInterface extends ConfigurableClassInterface
{
    /**
     * @return \MongoDB\Driver\ReadPreference
     */
    public static function getDefaultReadPreference();

    /**
     * @param ReadPreference $readPreference
     * @return bool
     */
    public static function supportsReadPreference(ReadPreference $readPreference);
}