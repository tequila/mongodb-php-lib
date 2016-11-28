<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class ListCollectionsFilterResolver extends OptionsResolver
{
    use CachedResolverTrait;

    public function configureOptions()
    {
        $this->setDefined([
            'name',
            'options.capped',
            'options.autoIndexId',
            'options.size',
            'options.max',
            'options.flags',
            'options.storageEngine',
        ]);

        $this
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('options.capped', 'bool')
            ->setAllowedTypes('options.size', 'integer')
            ->setAllowedTypes('options.max', 'integer')
            ->setAllowedTypes('options.storageEngine', ['array', 'object']);
    }
}