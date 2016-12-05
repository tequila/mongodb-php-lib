<?php

namespace Tequila\MongoDB\OptionsResolver\BulkWrite;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class UpdateResolver extends OptionsResolver
{
    use CachedResolverTrait;

    public function configureOptions()
    {
        CollationConfigurator::configure($this);

        $this->setDefined([
            'upsert',
            'multi',
        ]);

        $this->setAllowedTypes('upsert', 'bool');
        $this->setAllowedTypes('multi', 'bool');
    }
}