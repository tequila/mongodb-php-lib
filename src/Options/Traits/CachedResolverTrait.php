<?php

namespace Tequila\MongoDB\Options\Traits;

use Tequila\MongoDB\Options\OptionsResolver;

trait CachedResolverTrait
{
    /**
     * @var OptionsResolver
     */
    private static $cachedResolver;

    /**
     * @param array $options
     * @return array
     */
    private static function resolve(array $options)
    {
        return self::getResolver()->resolve($options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private static function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @return OptionsResolver
     */
    private static function getResolver()
    {
        if (!self::$cachedResolver) {
            self::$cachedResolver = new OptionsResolver();
            self::configureOptions(self::$cachedResolver);
        }

        return self::$cachedResolver;
    }
}