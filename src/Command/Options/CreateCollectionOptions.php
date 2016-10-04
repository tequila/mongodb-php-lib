<?php

namespace Tequila\MongoDB\Command\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class CreateCollectionOptions implements OptionsInterface
{
    use CachedResolverTrait;

    /**
     * @param  OptionsResolver $resolver
     */
    public static function configureOptions(OptionsResolver $resolver)
    {
        CommonOptions::configureOptions($resolver);

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
            if ($value && isset($options['capped']) && false === $options['capped']) {
                throw new InvalidArgumentException(
                    'The "size" and "max" options are meaningless until "capped" option has been set to true'
                );
            }

            return $value;
        };

        $resolver->setNormalizer('size', $sizeAndMaxOptionsNormalizer);
        $resolver->setNormalizer('max', $sizeAndMaxOptionsNormalizer);
    }
}