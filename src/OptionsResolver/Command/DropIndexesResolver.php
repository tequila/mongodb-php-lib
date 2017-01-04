<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DropIndexesResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        WriteConcernConfigurator::configure($this);
    }
}