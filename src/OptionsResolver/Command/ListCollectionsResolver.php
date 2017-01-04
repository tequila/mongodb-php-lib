<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class ListCollectionsResolver extends OptionsResolver
{
    public function resolve(array $options = [])
    {
        $options = parent::resolve($options);
        if (isset($options['filter'])) {
            $options['filter'] = (object)ListCollectionsFilterResolver::resolveStatic($options['filter']);
        }

        return $options;
    }

    protected function configureOptions()
    {
        $this->setDefined(['filter']);
        $this->setAllowedTypes('filter', ['array', 'object']);
    }
}