<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class WritingCommandOptions implements OptionsInterface
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('writeConcern');
        $resolver->setAllowedTypes('writeConcern', WriteConcern::class);
    }

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }
}