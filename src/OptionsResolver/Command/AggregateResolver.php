<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\OptionsResolver\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class AggregateResolver extends OptionsResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $options = [])
    {
        $hasOutStage = $this->hasOutStage($options);

        if (
            $hasOutStage
            && $this->hasDefault('readConcern')
            && $this->getDefault('readConcern') instanceof ReadConcern
            && ReadConcern::MAJORITY === $this->getDefault('readConcern')->getLevel()
        ) {
            $this->removeDefault('readConcern');
        }

        if (!$hasOutStage && $this->hasDefault('writeConcern')) {
            $this->removeDefault('writeConcern');
        }

        $options = parent::resolve($options);

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

        if ($hasOutStage) {
            $options['readPreference'] = new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        if ($options['useCursor']) {
            $options['cursor'] = [];

            if (isset($options['batchSize'])) {
                $options['cursor']['batchSize'] = $options['batchSize'];
                unset($options['batchSize']);
            }

            $options['cursor'] = (object) $options['cursor'];
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

    protected function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);

        $this
            ->setRequired('pipeline')
            ->setAllowedTypes('pipeline', 'array');

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
     * @param array $options
     *
     * @return bool
     */
    private function hasOutStage(array $options)
    {
        if (!array_key_exists('pipeline', $options)) {
            // Let resolve() to throw exception, since this option is required
            return false;
        }

        $pipeline = (array) $options['pipeline'];
        $lastStage = end($pipeline);

        return '$out' === key($lastStage);
    }
}
