<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\TypeMapConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DistinctResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        CollationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);
        TypeMapConfigurator::configure($this);
    }
}
