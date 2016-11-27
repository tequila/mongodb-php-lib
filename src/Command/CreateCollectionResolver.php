<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\WriteConcernCompatibilityTrait;
use Tequila\MongoDB\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\OptionsResolver;

class CreateCollectionResolver extends OptionsResolver implements
    WriteConcernAwareInterface,
    CompatibilityResolverInterface
{
    use WriteConcernTrait;
    use WriteConcernCompatibilityTrait;

    /**
     * @inheritdoc
     */
    public function configureOptions()
    {
        $this->setDefined([
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

        $this
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

        $sizeAndMaxOptionsNormalizerFactory = function ($optionName) {
            return function (Options $options, $value) use ($optionName) {
                if ($value && (!isset($options['capped']) || false === $options['capped'])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The "%s" option is meaningless until "capped" option has been set to true',
                            $optionName
                        )
                    );
                }

                return $value;
            };
        };

        $this->setNormalizer('size', $sizeAndMaxOptionsNormalizerFactory('size'));
        $this->setNormalizer('max', $sizeAndMaxOptionsNormalizerFactory('max'));
    }

    /**
     * @inheritdoc
     */
    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);
        if (!isset($options['size']) && isset($options['capped']) && true === $options['capped']) {
            throw new InvalidArgumentException(
                'The option "size" is required for capped collections'
            );
        }
        
        return $options;
    }
}