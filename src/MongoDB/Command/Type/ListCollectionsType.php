<?php

namespace Tequilla\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandTypeInterface;

/**
 * Class ListCollectionsType
 * @package Tequilla\MongoDB\Command\Type
 */
class ListCollectionsType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('listCollections', 1);
        $resolver->setAllowedValues('listCollections', [1]);
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