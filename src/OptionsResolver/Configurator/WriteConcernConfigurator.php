<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class WriteConcernConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('writeConcern')
            ->setAllowedTypes('writeConcern', WriteConcern::class);
    }
}