<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class CollectionOptions
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        DatabaseOptions::configureOptions($resolver);

        $resolver
            ->setDefined('typeMap')
            ->setAllowedTypes('typeMap', 'array')
            ->setDefault('typeMap', function(Options $options) {
                return TypeMapOptions::resolve([]);
            })
            ->setNormalizer('typeMap', function(Options $options, array $typeMap) {
                return TypeMapOptions::resolve($typeMap);
            });
    }

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }
}