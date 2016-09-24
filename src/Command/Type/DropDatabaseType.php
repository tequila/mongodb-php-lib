<?php

namespace Tequila\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Command\CommandTypeInterface;

class DropDatabaseType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;
        
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dropDatabase', 1);
        $resolver->setAllowedValues('dropDatabase', [1]);
    }
    
    public static function getCommandName()
    {
        return 'dropDatabase';
    }
}