<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class Delete implements WriteModelInterface
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
    public function __construct(array $filter, array $options = [])
    {
        $this->filter = $filter;
        $this->options = self::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->delete($this->filter, $this->options);
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);

        $resolver
            ->setDefined('limit')
            ->setAllowedValues('limit', [0, 1]);
    }
}