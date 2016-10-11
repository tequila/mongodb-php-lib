<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DriverOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
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
}