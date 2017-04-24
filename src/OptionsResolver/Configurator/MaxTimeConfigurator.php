<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class MaxTimeConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('maxTimeMS')
            ->setAllowedTypes('maxTimeMS', 'integer');
    }
}
