<?php

namespace Tequila\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Command\CommandTypeInterface;

class ListIndexesType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(self::getCommandName());
        $resolver->setAllowedTypes(self::getCommandName(), 'string');
    }

    public static function getCommandName()
    {
        return 'listIndexes';
    }
}