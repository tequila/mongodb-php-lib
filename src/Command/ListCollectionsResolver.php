<?php

namespace Tequila\MongoDB\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\OptionsResolver;

class ListCollectionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        $this->setDefined(['filter']);
        $this->setAllowedTypes('filter', ['array', 'object']);
        $this->setNormalizer('filter', function(Options $options, $filter) {
            $filter = (array)$filter;
            $filter = ListCollectionsFilterResolver::getCachedInstance()->resolve($filter);

            return (object)$filter;
        });
    }
}