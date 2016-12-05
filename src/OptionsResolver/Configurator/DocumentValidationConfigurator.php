<?php

namespace Tequila\MongoDB\OptionsResolver\Configurator;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DocumentValidationConfigurator
{
    public static function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('bypassDocumentValidation')
            ->setAllowedTypes('bypassDocumentValidation', 'bool');
    }
}