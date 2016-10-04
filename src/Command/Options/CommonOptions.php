<?php

namespace Tequila\MongoDB\Command\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class CommonOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('typeMap');
        $resolver->setAllowedTypes('typeMap', 'array');
        $resolver->setNormalizer('typeMap', function(Options $options, $typeMap) {
            return TypeMapOptions::resolve($typeMap);
        });
    }
}