<?php

namespace Tequila\MongoDB\Options\Configurator;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Options\OptionsResolver;

class WriteConcernConfigurator
{
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('writeConcern');
        $resolver->setAllowedTypes('writeConcern', WriteConcern::class);
    }
}