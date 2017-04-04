<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class TypeMapConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('typeMap')
            ->setAllowedTypes('typeMap', 'array');
    }
}