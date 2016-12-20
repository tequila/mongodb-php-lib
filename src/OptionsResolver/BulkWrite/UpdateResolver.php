<?php

namespace Tequila\MongoDB\OptionsResolver\BulkWrite;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class UpdateResolver extends OptionsResolver
{
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