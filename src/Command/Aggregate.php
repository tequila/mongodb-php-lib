<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\CompatibilityResolver;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class Aggregate implements CommandInterface
{
    use CachedResolverTrait;

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
    private $compiledOptions;

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
        if (empty($pipeline)) {
            throw new InvalidArgumentException('$pipeline cannot be empty');
        }

        $this->collectionName = (string)$collectionName;
        $this->pipeline = $pipeline;
        $this->compileOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return CompatibilityResolver::getInstance(
            $serverInfo,
            $this->compiledOptions,
            [
                'collation',
                'writeConcern',
                'readConcern',
            ]
        )->resolve();
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
     * Validates input options and compiles them to format, acceptable by the low-level driver
     *
     * @param array $options
     */
    private function compileOptions(array $options)
    {
        $options = self::resolve($options);

        if (isset($options['readConcern'])) {
            /** @var ReadConcern $readConcern */
            $readConcern = $options['readConcern'];
            if (null === $readConcern->getLevel() || ($this->hasOutStage() && ReadConcern::MAJORITY === $readConcern->getLevel())
            ) {
                unset($options['readConcern']);
            } else {
                $options['readConcern'] = ['level' => $readConcern->getLevel()];
            }
        }

        if (isset($options['writeConcern'])) {
            if (!$this->hasOutStage()) {
                throw new InvalidArgumentException(
                    'Option "writeConcern" is meaningless until aggregation pipeline has $out stage'
                );
            }
        }

        $this->readPreference = isset($options['readPreference']) ? $options['readPreference'] : null;
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

        $cmd = [
            'aggregate' => $this->collectionName,
            'pipeline' => $this->pipeline,
        ];

        $this->compiledOptions = $cmd + $options;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);
        WritingCommandOptions::configureOptions($resolver);

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
                    'Option "typeMap" is not allowed when option "useCursor" is set to false'
                );
            }

            return TypeMapOptions::resolve($typeMap);
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