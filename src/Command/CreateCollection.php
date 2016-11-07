<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Options\CompatibilityResolver;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Server;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class CreateCollection implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;
    use WriteConcernTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param array $options
     */
    public function __construct($collectionName, array $options = [])
    {
        $this->collectionName = (string)$collectionName;
        $this->options = self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        return CompatibilityResolver::getInstance($server, $this->compileOptions($server))
            ->checkWriteConcern()
            ->resolve();
    }

    private function compileOptions(Server $server)
    {
        $options = $this->options;

        if (!isset($options['size']) && isset($options['capped']) && true === $options['capped']) {
            throw new InvalidArgumentException(
                'The option "size" is required for capped collections'
            );
        }

        if (!isset($options['writeConcern']) && $this->writeConcern && $server->supportsWriteConcern()) {
            $options['writeConcern'] = $this->writeConcern;
        }

        return ['create' => (string)$this->collectionName] + $options;
    }

    /**
     * @param  OptionsResolver $resolver
     */
    private static function configureOptions(OptionsResolver $resolver)
    {
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined(
            [
                'capped',
                'size',
                'max',
                'flags',
                'storageEngine',
                'validator',
                'validationLevel',
                'validationAction',
                'indexOptionDefaults',
            ]
        );

        $resolver
            ->setAllowedTypes('capped', 'bool')
            ->setAllowedTypes('size', 'integer')
            ->setAllowedTypes('max', 'integer')
            ->setAllowedTypes('flags', 'integer')
            ->setAllowedTypes('storageEngine', ['array', 'object'])
            ->setAllowedTypes('validator', ['array', 'object'])
            ->setAllowedValues(
                'validationLevel',
                [
                    'off',
                    'strict',
                    'moderate',
                ]
            )
            ->setAllowedValues(
                'validationAction',
                [
                    'error',
                    'warn',
                ]
            )
            ->setAllowedTypes('indexOptionDefaults', ['array', 'object']);

        $sizeAndMaxOptionsNormalizerFactory = function ($optionName) {
            return function (Options $options, $value) use ($optionName) {
                if ($value && (!isset($options['capped']) || false === $options['capped'])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The "%s" option is meaningless until "capped" option has been set to true',
                            $optionName
                        )
                    );
                }

                return $value;
            };
        };

        $resolver->setNormalizer('size', $sizeAndMaxOptionsNormalizerFactory('size'));
        $resolver->setNormalizer('max', $sizeAndMaxOptionsNormalizerFactory('max'));
    }
}