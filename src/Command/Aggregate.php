<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\AggregationCursor;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;
use Tequila\MongoDB\Options\OptionsResolver;

class Aggregate implements CommandInterface
{
    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $pipeline;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ReadPreference|null
     */
    private $readPreference;

    /**
     * @var array
     */
    private $typeMap = [];

    /**
     * @var bool
     */
    private $useCursor;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $pipeline
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $pipeline, array $options = [])
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->pipeline = $pipeline;
        $this->options = $this->resolve($options);
    }

    /**
     * @param Manager $manager
     * @return AggregationCursor
     */
    public function execute(Manager $manager)
    {
        $options = ['aggregate' => $this->collectionName, 'pipeline' => $this->pipeline];
        $options += $this->options;
        $command = new Command($options);

        $cursor = $manager->executeCommand($this->databaseName, $command, $this->readPreference);

        return new AggregationCursor($cursor, $this->useCursor, $this->typeMap);
    }

    /**
     * @param array $options
     * @return array
     */
    private function resolve(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        if (isset($options['readConcern'])) {
            /** @var ReadConcern $readConcern */
            $readConcern = $options['readConcern'];
            if (null === $readConcern->getLevel() || ($this->hasOutStage() && ReadConcern::MAJORITY === $readConcern->getLevel())) {
                unset($options['readConcern']);
            } else {
                $options['readConcern'] = ['level' => $readConcern->getLevel()];
            }
        }

        $this->readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;
        unset($options['readPreference']);

        if (isset($options['typeMap'])) {
            $this->typeMap = $options['typeMap'];
            unset($options['typeMap']);
        }

        $this->useCursor = $options['useCursor'];
        unset($options['useCursor']);

        if ($this->useCursor) {
            if (isset($options['batchSize'])) {
                $options['cursor'] = ['batchSize' => $options['batchSize']];
                unset ($options['batchSize']);
            } else {
                $options['cursor'] = new \stdClass();
            }
        }

        return $options;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'allowDiskUse',
            'batchSize',
            'bypassDocumentValidation',
            'maxTimeMS',
            'readConcern',
            'readPreference',
            'typeMap',
            'useCursor',
        ]);

        $resolver
            ->setAllowedTypes('allowDiskUse', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
            ->setAllowedTypes('typeMap', 'array')
            ->setAllowedTypes('useCursor', 'bool');

        $resolver->setDefault('useCursor', true);

        $resolver->setNormalizer('batchSize', function(Options $options, $batchSize) {
            if (!isset($options['useCursor']) || false === $options['useCursor']) {
                throw new InvalidArgumentException(
                    'Option "batchSize" is meaningless unless option "useCursor" is set to true'
                );
            }

            return $batchSize;
        });

        $resolver->setNormalizer('readPreference', function(Options $options, ReadPreference $readPreference) {
            if ($this->hasOutStage() && ReadPreference::RP_PRIMARY !== $readPreference->getMode()) {
                return new ReadPreference(ReadPreference::RP_PRIMARY);
            }

            return $readPreference;
        });

        $resolver->setNormalizer('typeMap', function(Options $options, array $typeMap) {
            if (false === $options['useCursor']) {
                throw new InvalidArgumentException(
                    'Option "typeMap" will not get applied when option "useCursor" is set to false'
                );
            }

            return TypeMapOptions::resolve($typeMap);
        });
    }

    private function hasOutStage()
    {
        $lastStage = end($this->pipeline);

        return '$out' === key($lastStage);
    }

}