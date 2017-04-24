<?php

namespace Tequila\MongoDB\OptionsResolver;

use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\TypeMapConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;

class CollectionOptionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);
        TypeMapConfigurator::configure($this);
    }
}
