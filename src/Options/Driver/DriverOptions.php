<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DriverOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('typeMap')
            ->setAllowedTypes('typeMap', 'array')
            ->setDefault('typeMap', TypeMapOptions::getDefaultTypeMap())
            ->setNormalizer('typeMap', function(Options $options, array $typeMap) {
                return TypeMapOptions::resolve($typeMap);
            });
    }
}