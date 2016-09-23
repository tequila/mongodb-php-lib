<?php

namespace Tequila\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class UpdateOptions implements ConfigurableInterface
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