<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;

class ReadConcernOptions implements ConfigurableInterface
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