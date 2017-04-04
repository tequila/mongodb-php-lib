<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\TypeMapConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class FindAndModifyResolver extends OptionsResolver
{
    public function resolve(array $options = [])
    {
        $options = parent::resolve($options);

        if ((!isset($options['remove']) || false === $options['remove']) && !isset($options['update'])) {
            throw new InvalidArgumentException(
                'Option "update" is required when option "remove" is not set or set to false.'
            );
        }

        if (isset($options['remove']) && true === $options['remove'] && isset($options['update'])) {
            throw new InvalidArgumentException(
                'Option "update" is not allowed when option "remove" is set to true.'
            );
        }

        return $options;
    }

    protected function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);
        TypeMapConfigurator::configure($this);

        $this->setDefined([
            'sort',
            'remove',
            'update',
            'new',
            'fields',
            'upsert',
        ]);

        $documentTypes = ['array', 'object'];

        $this
            ->setAllowedTypes('sort', $documentTypes)
            ->setAllowedTypes('remove', 'bool')
            ->setAllowedTypes('update', $documentTypes)
            ->setAllowedTypes('new', 'bool')
            ->setAllowedTypes('fields', $documentTypes)
            ->setAllowedTypes('upsert', 'bool');
    }
}