<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class CollationConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('collation')
            ->setAllowedTypes('collation', ['array', 'object'])
            ->setNormalizer('collation', function ($collation) {
                return (object) $collation;
            });
    }
}
