<?php

namespace Tequila\MongoDB\Options\Configurator;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Options\OptionsResolver;

class ReadPreferenceConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('readPreference')
            ->setAllowedTypes('readPreference', ReadPreference::class);
    }
}