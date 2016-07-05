<?php

namespace Tequilla\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandTypeInterface;

class CreateCollectionType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'create',
            'capped',
            'size',
            'max',
            'flags',
            'storageEngine',
            'validator',
            'validationLevel',
            'validationAction',
            'indexOptionDefaults',
        ]);

        $resolver->setAllowedTypes('create', 'string');
        $resolver->setAllowedTypes('capped', 'bool');
        $resolver->setDefault('capped', false);
        $resolver->setAllowedTypes('size', 'integer');
        $resolver->setAllowedTypes('max', 'integer');
        $resolver->setAllowedTypes('flags', 'integer');
        $resolver->setAllowedTypes('storageEngine', ['array', 'object']);
        $resolver->setAllowedTypes('validator', ['array', 'object']);
        $resolver->setAllowedValues('validationLevel', [
            'off',
            'strict',
            'moderate',
        ]);
        $resolver->setAllowedValues('validationAction', [
            'error',
            'warn',
        ]);
        $resolver->setAllowedTypes('indexOptionDefaults', ['array', 'object']);
    }
}