<?php

namespace Tequilla\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandTypeInterface;

/**
 * Class DropCollectionType
 * @package Tequilla\MongoDB\Command\Type
 */
class DropCollectionType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;
    
    public static function getCommandName()
    {
        return 'drop';
    }
    
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('drop');
        $resolver->setAllowedTypes('drop', 'string');
    }
}