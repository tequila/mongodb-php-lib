<?php

namespace Tequila\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Command\CommandTypeInterface;

/**
 * Class DropCollectionType
 * @package Tequila\MongoDB\Command\Type
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