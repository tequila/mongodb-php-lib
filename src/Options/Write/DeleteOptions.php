<?php

namespace Tequilla\MongoDB\Options\Write;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

class DeleteOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['limit', 'collation']);
        $resolver->setAllowedValues('limit', [0, 1]);
    }
}