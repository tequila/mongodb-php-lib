<?php

namespace Tequila\MongoDB\Write\Options;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class UpdateOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'upsert',
            'multi',
            'collation',
        ]);

        $resolver->setAllowedTypes('upsert', 'bool');
        $resolver->setAllowedTypes('multi', 'bool');
        $resolver->setDefaults([
            'upsert' => false,
            'multi' => false,
        ]);
    }
}