<?php

namespace Tequilla\MongoDB\Options\Traits;

use Symfony\Component\OptionsResolver\OptionsResolver;

trait CachedResolverTrait
{
    private static $cachedResolver;

    public static function getCachedResolver()
    {
        if (!self::$cachedResolver) {
            self::$cachedResolver = new OptionsResolver();
            self::configureOptions(self::$cachedResolver);
        }

        return self::$cachedResolver;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
    }
}