<?php

namespace Tequilla\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandTypeInterface;

class CreateIndexesType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(self::getCommandName())
            ->setAllowedTypes(self::getCommandName(), 'string');

        $resolver
            ->setRequired('indexes')
            ->setAllowedTypes('indexes', 'array');
    }

    public static function getCommandName()
    {
        return 'createIndexes';
    }
}