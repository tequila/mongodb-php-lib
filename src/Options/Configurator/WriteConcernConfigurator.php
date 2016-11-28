<?php

namespace Tequila\MongoDB\Options\Configurator;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Options\OptionsResolver;

class WriteConcernConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('writeConcern')
            ->setAllowedTypes('writeConcern', WriteConcern::class);
    }
}