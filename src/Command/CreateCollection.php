<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class CreateCollection implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

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
        $this->options = ['create' => (string)$collectionName] + self::compileOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }

    private static function compileOptions(array $options)
    {
        $options = self::resolve($options);

        if (!isset($options['size']) && isset($options['capped']) && true === $options['capped']) {
            throw new InvalidArgumentException(
                'The option "size" is required for capped collections'
            );
        }

        return $options;
    }

    /**
     * @param  OptionsResolver $resolver
     */
    private static function configureOptions(OptionsResolver $resolver)
    {
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'capped',
            'size',
            'max',
            'flags',
            'storageEngine',
            'validator',
            'validationLevel',
            'validationAction',
            'indexOptionDefaults',
        ]);

        $resolver
            ->setAllowedTypes('capped', 'bool')
            ->setAllowedTypes('size', 'integer')
            ->setAllowedTypes('max', 'integer')
            ->setAllowedTypes('flags', 'integer')
            ->setAllowedTypes('storageEngine', ['array', 'object'])
            ->setAllowedTypes('validator', ['array', 'object'])
            ->setAllowedValues('validationLevel', [
                'off',
                'strict',
                'moderate',
            ])
            ->setAllowedValues('validationAction', [
                'error',
                'warn',
            ])
            ->setAllowedTypes('indexOptionDefaults', ['array', 'object']);

        $sizeAndMaxOptionsNormalizerFactory = function($optionName) {
            return function(Options $options, $value) use($optionName) {
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