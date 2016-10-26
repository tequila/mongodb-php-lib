<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\ServerInfo;

class ListCollections implements CommandInterface
{
    use CachedResolverTrait;
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = ['listCollections' => 1] + self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['filter']);
        $resolver->setAllowedTypes('filter', ['array', 'object']);
        $resolver->setNormalizer('filter', function(Options $options, $filter) {
            $filter = (array)$filter;
            $filterResolver = new OptionsResolver();
            $filterResolver->setDefined([
                'name',
                'options.capped',
                'options.autoIndexId',
                'options.size',
                'options.max',
                'options.flags',
                'options.storageEngine',
            ]);

            $value = $filterResolver->resolve($filter);

            return (object)$value;
        });
    }
}