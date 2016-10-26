<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class DatabaseOptions
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'readConcern',
            'readPreference',
            'writeConcern',
        ]);

        $resolver
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
            ->setAllowedTypes('writeConcern', WriteConcern::class);
    }

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }
}