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

        $this
            ->setRequired('pipeline')
            ->setAllowedTypes('pipeline', ['array', 'object'])
            ->setNormalizer('pipeline', function(Options $options, $pipeline) {
                return (object)$pipeline;
            });

        $this->setDefined([
            'allowDiskUse',
            'batchSize',
            'maxTimeMS',
            'useCursor',
        ]);

        $this
            ->setAllowedTypes('allowDiskUse', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('useCursor', 'bool');

        $this->setDefault('useCursor', true);
    }

    /**
     * @inheritdoc
     */
    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);

        return $this->compile($options);
    }

    /**
     * @inheritdoc
     */
    public function resolveCompatibilities(CommandOptions $options)
    {
        $options
            ->resolveReadConcern($this->readConcern)
            ->resolveCollation()
            ->resolveDocumentValidation();

        if ($this->hasOutStage($options)) {
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
     * @param array $options
     * @return array
     */
    private function compile(array $options)
    {
        $hasOutStage = $this->hasOutStage($options);

        if (isset($options['readConcern'])) {
            /** @var ReadConcern $readConcern */
            $readConcern = $options['readConcern'];
            if ($hasOutStage && ReadConcern::MAJORITY === $readConcern->getLevel()) {
                throw new InvalidArgumentException(
                    'Specifying "readConcern" option with "majority" level is prohibited when pipeline has $out stage'
                );
            }
        }

        if (isset($options['writeConcern']) && !$hasOutStage) {
            throw new InvalidArgumentException(
                'Option "writeConcern" is meaningless until aggregation pipeline has $out stage'
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
                    'Option "batchSize" is meaningless unless option "useCursor" is set to true'
                );
            }
        }
        unset($options['useCursor']);

        $cmd = [
            'aggregate' => $options['aggregate'],
            'pipeline' => $options['pipeline'],
        ];

        return $cmd + $options;
    }

    /**
     * @param array|CommandOptions $options
     * @return bool
     */
    private function hasOutStage($options)
    {
        $lastStage = end($options['pipeline']);

        return '$out' === key($lastStage);
    }
}