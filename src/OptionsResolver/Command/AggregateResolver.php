<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\OptionsResolver\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\OptionsResolver\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\CommandOptions;

class AggregateResolver
    extends OptionsResolver
    implements
    CompatibilityResolverInterface,
    ReadConcernAwareInterface,
    WriteConcernAwareInterface,
    ReadPreferenceResolverInterface
{
    use ReadConcernTrait;
    use WriteConcernTrait;

    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);

        $this
            ->setRequired('pipeline')
            ->setAllowedTypes('pipeline', ['array', 'object'])
            ->setNormalizer('pipeline', function(Options $options, $pipeline) {
                return (object)$pipeline;
            });

        $this->setDefined([
            'allowDiskUse',
            'batchSize',
            'useCursor',
        ]);

        $this
            ->setAllowedTypes('allowDiskUse', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('useCursor', 'bool');

        $this->setDefault('useCursor', true);
    }

    /**
     * @inheritdoc
     */
    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);

        $hasOutStage = $this->hasOutStage($options);

        if (isset($options['readConcern'])) {
            /** @var ReadConcern $readConcern */
            $readConcern = $options['readConcern'];
            if ($hasOutStage && ReadConcern::MAJORITY === $readConcern->getLevel()) {
                throw new InvalidArgumentException(
                    'Specifying "readConcern" option with "majority" level is prohibited when pipeline has $out stage.'
                );
            }
        }

        if (isset($options['writeConcern']) && !$hasOutStage) {
            throw new InvalidArgumentException(
                'Option "writeConcern" is meaningless until aggregation pipeline has $out stage.'
            );
        }

        if ($options['useCursor']) {
            $options['cursor'] = [];

            if (isset($options['batchSize'])) {
                $options['cursor']['batchSize'] = $options['batchSize'];
                unset($options['batchSize']);
            }

            $options['cursor'] = (object)$options['cursor'];
        } else {
            if (isset($options['batchSize'])) {
                throw new InvalidArgumentException(
                    'Option "batchSize" is meaningless unless option "useCursor" is set to true.'
                );
            }
        }
        unset($options['useCursor']);

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function resolveCompatibilities(CommandOptions $options)
    {
        $options
            ->resolveCollation()
            ->resolveDocumentValidation();

        $hasOutStage = $this->hasOutStage($options);

        if ($this->readConcern) {
            if (!($hasOutStage && ReadConcern::MAJORITY === $this->readConcern->getLevel())) {
                $options->resolveReadConcern($this->readConcern);
            }
        }

        if ($hasOutStage) {
            $options->resolveWriteConcern($this->writeConcern);
        }
    }

    /**
     * @inheritdoc
     */
    public function resolveReadPreference(array $options, ReadPreference $defaultReadPreference)
    {
        if ($this->hasOutStage($options)) {
            return new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        return isset($options['readPreference']) ? $options['readPreference'] : $defaultReadPreference;
    }

    /**
     * @param array|CommandOptions $options
     * @return bool
     */
    private function hasOutStage($options)
    {
        $pipeline = (array)$options['pipeline'];
        $lastStage = end($pipeline);

        return '$out' === key($lastStage);
    }
}