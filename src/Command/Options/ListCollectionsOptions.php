<?php

namespace Tequila\MongoDB\Command\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class ListCollectionsOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        CommonOptions::configureOptions($resolver);

        $resolver->setDefined(['filter']);
        $resolver->setAllowedTypes('filter', ['array', 'object']);
        $resolver->setNormalizer('filter', function(Options $options, $value) {
            $value = (array) $value;
            $filterResolver = new OptionsResolver();
            $filterResolver->setDefined([
                'name',
                'options.capped',
                'options.autoIndexId',
                'options.size',
                'options.max',
                'options.flags',
                'options.storageEngine',
            ]);

            $value = $filterResolver->resolve($value);

            return (object) $value;
        });
    }
}