<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class CreateCollectionResolver extends OptionsResolver
{
    /**
     * @inheritdoc
     */
    public function resolve(array $options = [])
    {
        $options = parent::resolve($options);

        if (!isset($options['size']) && isset($options['capped']) && true === $options['capped']) {
            throw new InvalidArgumentException(
                'The option "size" is required for capped collections.'
            );
        }

        foreach (['max', 'size'] as $optionName) {
            if (isset($options[$optionName]) && (!isset($options['capped']) || false === $options['capped'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The "%s" option is meaningless until "capped" option has been set to true.',
                        $optionName
                    )
                );
            }
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    protected function configureOptions()
    {
        WriteConcernConfigurator::configure($this);

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
    }
}