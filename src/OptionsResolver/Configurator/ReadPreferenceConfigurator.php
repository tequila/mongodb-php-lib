<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class ReadPreferenceConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('readPreference')
            ->setAllowedTypes('readPreference', ReadPreference::class);
    }
}