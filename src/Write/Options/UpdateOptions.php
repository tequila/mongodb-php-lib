<?php

namespace Tequilla\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

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