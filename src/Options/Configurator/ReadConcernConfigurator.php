<?php

namespace Tequila\MongoDB\Options\Configurator;

use MongoDB\Driver\ReadConcern;
use Tequila\MongoDB\Options\OptionsResolver;

class ReadConcernConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('readConcern')
            ->setAllowedTypes('readConcern', ReadConcern::class);
    }
}