<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DriverOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        TypeMapOptions::configureOptions($resolver);
    }
}