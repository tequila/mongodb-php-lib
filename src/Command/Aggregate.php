<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\ConvertWriteConcernToDocumentTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Traits\EnsureCollationOptionSupportedTrait;
use Tequila\MongoDB\Traits\EnsureReadConcernOptionSupported;
use Tequila\MongoDB\Traits\EnsureWriteConcernOptionSupported;

class Aggregate implements CommandInterface
{
    use ConvertWriteConcernToDocumentTrait;
    use EnsureCollationOptionSupportedTrait;
    use EnsureWriteConcernOptionSupported;
    use EnsureReadConcernOptionSupported;

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
     * @param string $collectionName
     * @param array $pipeline
     * @param array $options
     */
    public function __construct($collectionName, array $pipeline, array $options = [])
    {
        $this->pipeline = $pipeline;
        $this->options = [
                'aggregate' => $collectionName,
                'pipeline' => $this->pipeline
            ] + $this->resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        if (array_key_exists('collation', $this->options)) {
            $this->ensureCollationOptionSupported($serverInfo);
        }

        if (array_key_exists('writeConcern', $this->options)) {
            $this->ensureWriteConcernOptionSupported($serverInfo);
        }

        if (array_key_exists('readConcern', $this->options)) {
            $this->ensureReadConcernOptionSupported($serverInfo);
        }

        return $this->options;
    }

    /**
     * @return ReadPreference|null
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    /**
     * @return bool
     */
    public function getUseCursor()
    {
        return $this->useCursor;
    }

    /**
     * @return array
     */
    public function getTypeMap()
    {
        return $this->typeMap;
    }

    /**
     * @inheritdoc
     */
    public function needsPrimaryServer()
    {
        return $this->hasOutStage();
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
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'allowDiskUse',
            'batchSize',
            'bypassDocumentValidation',
            'collation',
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
            ->setAllowedTypes('collation', ['array', 'object'])
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
            ->setAllowedTypes('typeMap', 'array')
            ->setAllowedTypes('useCursor', 'bool');

        $resolver->setDefault('useCursor', true);

        $resolver->setNormalizer('batchSize', function (Options $options, $batchSize) {
            if (!isset($options['useCursor']) || false === $options['useCursor']) {
                throw new InvalidArgumentException(
                    'Option "batchSize" is meaningless unless option "useCursor" is set to true'
                );
            }

            return $batchSize;
        });

        $resolver->setNormalizer('typeMap', function (Options $options, array $typeMap) {
            if (false === $options['useCursor']) {
                throw new InvalidArgumentException(
                    'Option "typeMap" will not get applied when option "useCursor" is set to false'
                );
            }

            return TypeMapOptions::resolve($typeMap);
        });

        $resolver->setNormalizer('writeConcern', function (Options $options, WriteConcern $writeConcern) {
            if (!$this->hasOutStage()) {
                throw new InvalidArgumentException(
                    'Options "writeConcern" is meaningless until aggregation pipeline has $out stage'
                );
            }

            return self::convertWriteConcernToDocument($writeConcern);
        });
    }

    /**
     * @return bool
     */
    private function hasOutStage()
    {
        $lastStage = end($this->pipeline);

        return '$out' === key($lastStage);
    }
}