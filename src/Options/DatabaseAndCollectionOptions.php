<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class DatabaseAndCollectionOptions implements OptionsInterface
{
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'readConcern',
            'readPreference',
            'writeConcern',
            'typeMap',
        ]);

        $resolver
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
            ->setAllowedTypes('writeConcern', WriteConcern::class)
            ->setAllowedTypes('typeMap', 'array');

        $resolver->setDefault('typeMap', TypeMapOptions::getDefaultTypeMap());

        $resolver->setNormalizer('typeMap', function(Options $options, array $typeMap) {
            return TypeMapOptions::resolve($typeMap);
        });
    }

    public static function resolve(array $options, Manager $manager)
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);
        $resolver->setDefaults([
            'readConcern' => $manager->getReadConcern(),
            'readPreference' => $manager->getReadPreference(),
            'writeConcern' => $manager->getWriteConcern(),
        ]);

        return $resolver->resolve($options);
    }
}