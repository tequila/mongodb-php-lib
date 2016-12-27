<?php

namespace Tequila\MongoDB\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver as BaseResolver;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Tequila\MongoDB\Exception\InvalidArgumentException;

abstract class OptionsResolver extends BaseResolver
{
    /**
     * @var OptionsResolver[]
     */
    private static $cache = [];

    public function resolve(array $options = array())
    {
        try {
            return parent::resolve($options);
        } catch (OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    protected function configureOptions()
    {
    }

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

            /** @var OptionsResolver $resolver */
            $resolver = new $resolverClass;
            if (!$resolver instanceof self) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Resolver class "%s" must extend "%s".',
                        $resolverClass,
                        self::class
                    )
                );
            }

            $resolver->configureOptions();

            self::$cache[$resolverClass] = new $resolverClass;
        }

        return self::$cache[$resolverClass];
    }
}