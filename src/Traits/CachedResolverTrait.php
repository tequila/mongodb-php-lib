<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

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
            self::$cachedInstance->configureOptions();
        }

        return self::$cachedInstance;
    }

    abstract public function configureOptions();
}