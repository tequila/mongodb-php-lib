<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DatabaseOptions implements OptionsInterface
{
    use CachedResolverTrait;

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
}