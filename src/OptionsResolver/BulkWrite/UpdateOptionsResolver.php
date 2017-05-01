<?php

namespace Tequila\MongoDB\OptionsResolver\BulkWrite;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class UpdateOptionsResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        CollationConfigurator::configure($this);

        $this->setDefined([
            'upsert',
            'multi',
        ]);

        $this
            ->setAllowedTypes('upsert', 'bool')
            ->setAllowedTypes('multi', 'bool');
    }
}
