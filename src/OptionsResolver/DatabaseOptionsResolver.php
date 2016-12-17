<?php

namespace Tequila\MongoDB\OptionsResolver;

use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;

class DatabaseOptionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);
    }
}