<?php

namespace Tequilla\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandTypeInterface;

/**
 * Class DropDatabaseType
 * @package Tequilla\MongoDB\Command\Type
 */
class DropDatabaseType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;
        
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dropDatabase', 1);
        $resolver->setAllowedValues('dropDatabase', [1]);
    }
}