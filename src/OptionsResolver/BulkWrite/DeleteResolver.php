<?php

namespace Tequila\MongoDB\OptionsResolver\BulkWrite;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DeleteResolver extends OptionsResolver
{
    public function configureOptions()
    {
        CollationConfigurator::configure($this);

        $this
            ->setDefined('limit')
            ->setAllowedValues('limit', [0, 1]);
    }
}