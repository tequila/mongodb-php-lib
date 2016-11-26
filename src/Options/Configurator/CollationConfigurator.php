<?php

namespace Tequila\MongoDB\Options\Configurator;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\OptionsResolver;

class CollationConfigurator
{
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('collation')
            ->setAllowedTypes('collation', ['array', 'object'])
            ->setNormalizer('collation', function(Options $options, $collation) {
                return (object)$collation;
            });
    }
}