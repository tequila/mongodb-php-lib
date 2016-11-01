<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Options\CompatibilityResolver;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\ServerInfo;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class Count implements CommandInterface
{
    use CachedResolverTrait;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter = [], array $options = [])
    {
        $this->filter = $filter;
        $this->options = self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return CompatibilityResolver::getInstance(
            $serverInfo,
            $this->options,
            ['readConcern']
        )->resolve();
    }

    /**
     * @inheritdoc
     */
    public function needsPrimaryServer()
    {
        return false;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'query',
            'limit',
            'skip',
            'hint',
            'readConcern',
        ]);

        $resolver
            ->setAllowedTypes('query', ['array', 'object'])
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('hint', ['string', 'array', 'object'])
            ->setAllowedTypes('readConcern', ReadConcern::class);

        $resolver->setNormalizer('hint', function(Options $options, $hint) {
            if (is_array($hint) || is_object($hint)) {
                $hint = Index::generateIndexName((array)$hint);
            }

            return $hint;
        });
    }
}