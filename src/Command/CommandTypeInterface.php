<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Options\ConfigurableInterface;

/**
 * Interface CommandInterface
 * @package Tequila\MongoDB\Command
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