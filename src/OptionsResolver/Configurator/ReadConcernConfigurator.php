<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use MongoDB\Driver\ReadConcern;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class ReadConcernConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('readConcern')
            ->setAllowedTypes('readConcern', ReadConcern::class);
    }
}
