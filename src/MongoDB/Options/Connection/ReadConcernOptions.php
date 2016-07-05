<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

class ReadConcernOptions implements ConfigurableClassInterface
{
    const READ_CONCERN_LEVEL = 'readconcernlevel';
    
    public static function getAll()
    {
        return [ self::READ_CONCERN_LEVEL ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
    }
}