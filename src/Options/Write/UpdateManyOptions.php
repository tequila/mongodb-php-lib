<?php

namespace Tequilla\MongoDB\Options\Write;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

class UpdateManyOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        UpdateOptions::configureOptions($resolver);
        $resolver->setAllowedValues('multi', true);
    }
}