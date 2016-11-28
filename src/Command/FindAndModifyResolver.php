<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\Configurator\CollationConfigurator;
use Tequila\MongoDB\Options\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\ServerCompatibleOptions;

class FindAndModifyResolver extends OptionsResolver implements WriteConcernAwareInterface, CompatibilityResolverInterface
{
    use WriteConcernTrait;

    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);

        $this->setDefined([
            'sort',
            'remove',
            'update',
            'new',
            'fields',
            'upsert',
            'maxTimeMS',
        ]);

        $documentTypes = ['array', 'object'];

        $this
            ->setAllowedTypes('sort', $documentTypes)
            ->setAllowedTypes('remove', 'bool')
            ->setAllowedTypes('update', $documentTypes)
            ->setAllowedTypes('new', 'bool')
            ->setAllowedTypes('fields', $documentTypes)
            ->setAllowedTypes('upsert', 'bool')
            ->setAllowedTypes('maxTimeMS', 'integer');
    }

    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);

        if ((!isset($options['remove']) || false === $options['remove']) && !isset($options['update'])) {
            throw new InvalidArgumentException(
                'Option "update" is required when option "remove" is not set or set to false'
            );
        }

        if (isset($options['remove']) && true === $options['remove'] && isset($options['update'])) {
            throw new InvalidArgumentException(
                'Option "update" is not allowed when option "remove" is set to true'
            );
        }

        return $options;
    }

    public function resolveCompatibilities(ServerCompatibleOptions $options)
    {
        $options
            ->resolveCollation()
            ->resolveDocumentValidation()
            ->resolveWriteConcern($this->writeConcern);
    }
}