<?php

namespace Tequila\MongoDB\OptionsResolver;

use Tequila\MongoDB\Exception\InvalidArgumentException;

class ResolverFactory
{
    /**
     * @var OptionsResolver[]
     */
    private static $cache = [];

    /**
     * @param string $resolverClass fully-qualified class name of resolver
     * @return OptionsResolver
     */
    public static function get($resolverClass)
    {
        if (!is_string($resolverClass)) {
            throw new InvalidArgumentException('$resolverClass must be a string.');
        }

        if (!array_key_exists($resolverClass, self::$cache)) {
            if (!class_exists($resolverClass)) {
                throw new InvalidArgumentException(
                    sprintf('Resolver class "%s" is not found.', $resolverClass)
                );
            }

            if (!is_subclass_of($resolverClass, OptionsResolver::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Resolver class "%s" must extend "%s".',
                        $resolverClass,
                        OptionsResolver::class
                    )
                );
            }

            /** @var OptionsResolver $resolver */
            $resolver = new $resolverClass;
            $resolver->configureOptions();

            self::$cache[$resolverClass] = new $resolverClass;
        }

        return self::$cache[$resolverClass];
    }
}