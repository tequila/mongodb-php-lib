<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\CommandOptions;

class FindAndModifyResolver extends OptionsResolver implements WriteConcernAwareInterface, CompatibilityResolverInterface
{
    use WriteConcernTrait;

    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);

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

    public function resolve(array $options = array())
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

    public function resolveCompatibilities(CommandOptions $options)
    {
        $options
            ->resolveCollation()
            ->resolveDocumentValidation();

        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        if (!$options->getServer()->supportsWireVersion(4)) {
            unset($options['writeConcern']);
        }
    }
}