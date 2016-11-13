<?php

namespace Tequila\MongoDB\Command\Traits;

trait CachedInstanceTrait
{
    private static $instance;

    /**
     * @return parent
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}