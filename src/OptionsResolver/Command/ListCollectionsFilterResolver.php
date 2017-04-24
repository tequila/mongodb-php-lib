<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class ListCollectionsFilterResolver extends OptionsResolver
{
    protected function configureOptions()
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
