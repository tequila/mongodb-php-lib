<?php

namespace Tequila\MongoDB\Command\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Command\CommandTypeInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;

class CreateCollectionType implements CommandTypeInterface
{
    use PrimaryReadPreferenceTrait;

    /**
     * @param  OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('create');
        
        $resolver->setDefined([
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

        $resolver
            ->setAllowedTypes('create', 'string')
            ->setAllowedTypes('capped', 'bool')
            ->setAllowedTypes('size', 'integer')
            ->setAllowedTypes('max', 'integer')
            ->setAllowedTypes('flags', 'integer')
            ->setAllowedTypes('storageEngine', ['array', 'object'])
            ->setAllowedTypes('validator', ['array', 'object'])
            ->setAllowedValues('validationLevel', [
                'off',
                'strict',
                'moderate',
            ])
            ->setAllowedValues('validationAction', [
                'error',
                'warn',
            ])
            ->setAllowedTypes('indexOptionDefaults', ['array', 'object']);

        $resolver->setDefault('size', function(Options $options) {
            if (!empty($options['capped'])) {
                throw new InvalidArgumentException(
                    'The option "size" is required for capped collections'
                );
            }

            return 0;
        });

        $sizeAndMaxOptionsNormalizer = function(Options $options, $value) {
            if ($value && empty($options['capped'])) {
                throw new InvalidArgumentException(
                    'The "size" and "max" options are meaningless until "capped" option has been set to true'
                );
            }

            return $value;
        };

        $resolver->setNormalizer('size', $sizeAndMaxOptionsNormalizer);
        $resolver->setNormalizer('max', $sizeAndMaxOptionsNormalizer);
    }

    public static function getCommandName()
    {
        return 'create';
    }
}
