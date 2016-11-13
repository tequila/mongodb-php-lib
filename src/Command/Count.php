<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Util\CompatibilityChecker;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Server;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class Count implements CommandInterface
{
    use CachedResolverTrait;
    use ReadConcernTrait;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ReadPreference
     */
    private $readPreference;

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
    public function getOptions(Server $server)
    {
        return CompatibilityChecker::getInstance($server, $this->options)
            ->checkReadConcern()
            ->resolve();
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
            'readPreference',
        ]);

        $resolver
            ->setAllowedTypes('query', ['array', 'object'])
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('hint', ['string', 'array', 'object'])
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class);

        $resolver->setNormalizer('hint', function(Options $options, $hint) {
            if (is_array($hint) || is_object($hint)) {
                $hint = Index::generateIndexName((array)$hint);
            }

            return $hint;
        });

        $resolver->setNormalizer('readPreference', function(Options $options, $readPreference) {
            $this->readPreference = $readPreference;
        });
    }
}