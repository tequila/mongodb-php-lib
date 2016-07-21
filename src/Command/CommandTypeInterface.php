<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\ReadPreference;
use Tequilla\MongoDB\Options\ConfigurableInterface;

/**
 * Interface CommandInterface
 * @package Tequilla\MongoDB\Command
 */
interface CommandTypeInterface extends ConfigurableInterface
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

    /**
     * @return string
     */
    public static function getCommandName();
}