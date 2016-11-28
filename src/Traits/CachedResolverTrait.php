<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Options\OptionsResolver;

trait CachedResolverTrait
{
    /**
     * @var OptionsResolver
     */
    private static $cachedInstance;

    /**
     * @return OptionsResolver
     */
    public static function getCachedInstance()
    {
        if (!self::$cachedInstance) {
            self::$cachedInstance = new self;
            self::configureOptions();
        }

        return self::$cachedInstance;
    }

    abstract public function configureOptions();
}