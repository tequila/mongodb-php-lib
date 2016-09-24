<?php

namespace Tequila\MongoDB\Options\Traits;

use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;

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
    public static function resolve(array $options)
    {
        try {
            return self::getResolver()->resolve($options);
        } catch (OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver)
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