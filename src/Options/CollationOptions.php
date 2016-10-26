<?php

namespace Tequila\MongoDB\Options;

class CollationOptions
{
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('collation')
            ->setAllowedTypes('collation', ['array', 'object']);
    }
}