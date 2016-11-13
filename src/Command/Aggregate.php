<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\CachedInstanceTrait;
use Tequila\MongoDB\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\Command\Traits\ReadPreferenceTrait;
use Tequila\MongoDB\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Util\CompatibilityChecker;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Server;

class Aggregate extends OptionsResolver implements ReadConcernAwareInterface, WriteConcernAwareInterface, ReadPreferenceAwareInterface
{
    use CachedInstanceTrait;
    use ReadConcernTrait;
    use ReadPreferenceTrait;
    use WriteConcernTrait;

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
     * @param string $collectionName
     * @param array $pipeline
     * @param array $options
     */
    public function __construct(
        $collectionName,
        array $pipeline,
        array $options = []
    )
    {
        if (empty($pipeline)) {
            throw new InvalidArgumentException('$pipeline cannot be empty');
        }

        $this->collectionName = (string)$collectionName;
        $this->pipeline = $pipeline;
        $this->options = self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        $options = $this->compileOptions($server);

        return CompatibilityChecker::getInstance($server, $options)
            ->checkCollation()
            ->checkReadConcern()
            ->checkWriteConcern()
            ->resolve();
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
     * @return bool
     */
    public function hasOutStage()
    {
        $lastStage = end($this->pipeline);

        return '$out' === key($lastStage);
    }

    /**
     * Compiled input options to format, acceptable by the low-level driver
     * @param Server $server
     * @return array
     */
    private function compileOptions(Server $server)
    {
        $options = $this->options;

        if (
            !isset($options['readConcern'])
            && $this->readConcern
            && !($this->hasOutStage() && ReadConcern::MAJORITY === $this->readConcern->getLevel())
            && $server->supportsReadConcern()
        ) {
            $options['readConcern'] = $this->readConcern;
        }

        if (isset($options['readConcern']) && $readConcern = $options['readConcern']) {
            /** @var ReadConcern $readConcern */
            if ($this->hasOutStage() && ReadConcern::MAJORITY === $readConcern->getLevel()) {
                throw new InvalidArgumentException(
                    'Specifying "readConcern" option with "majority" level is prohibited when pipeline has $out stage'
                );
            }

            if (null === $readConcern->getLevel()) {
                unset($options['readConcern']);
            }
        }

        if (
            !isset($options['writeConcern'])
            && $this->writeConcern
            && $this->hasOutStage()
            && $server->supportsWriteConcern()
        ) {
            $options['writeConcern'] = $this->writeConcern;
        }

        if (isset($options['writeConcern']) && !$this->hasOutStage()) {
            throw new InvalidArgumentException(
                'Option "writeConcern" is meaningless until aggregation pipeline has $out stage'
            );
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

        return $cmd + $options;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined(
            [
                'allowDiskUse',
                'batchSize',
                'bypassDocumentValidation',
                'maxTimeMS',
                'readConcern',
                'readPreference',
                'typeMap',
                'useCursor',
            ]
        );

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

        $resolver->setNormalizer(
            'batchSize',
            function (Options $options, $batchSize) {
                if (!isset($options['useCursor']) || false === $options['useCursor']) {
                    throw new InvalidArgumentException(
                        'Option "batchSize" is meaningless unless option "useCursor" is set to true'
                    );
                }

                return $batchSize;
            }
        );

        $resolver->setNormalizer(
            'typeMap',
            function (Options $options, array $typeMap) {
                if (false === $options['useCursor']) {
                    throw new InvalidArgumentException(
                        'Option "typeMap" is not allowed when option "useCursor" is set to false'
                    );
                }

                return TypeMapOptions::resolve($typeMap);
            }
        );
    }
}