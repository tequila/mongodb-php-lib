<?php

namespace Tequila\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Command\CommandTypeInterface;

class ListDatabasesType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('listDatabases', 1);
        $resolver->setAllowedValues('listDatabases', 1);
    }

    public static function getCommandName()
    {
        return 'listDatabases';
    }
}
