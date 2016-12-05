<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\OptionsResolver\Command\ListCollectionsFilterResolver;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;

class ListCollectionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        $this->setDefined(['filter']);
        $this->setAllowedTypes('filter', ['array', 'object']);
        $this->setNormalizer('filter', function(Options $options, $filter) {
            $filter = (array)$filter;
            $filter = ResolverFactory::get(ListCollectionsFilterResolver::class)->resolve($filter);

            return (object)$filter;
        });
    }
}