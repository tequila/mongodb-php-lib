<?php

namespace Tequila\MongoDB\Options\Configurator;

use Tequila\MongoDB\Options\OptionsResolver;

class DocumentValidationConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('bypassDocumentValidation')
            ->setAllowedTypes('bypassDocumentValidation', 'bool');
    }
}