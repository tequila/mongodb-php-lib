<?php

namespace Tequila\MongoDB\OptionsResolver\Command\Traits;

use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;

trait WriteConcernConfiguratorTrait
{
    public function configureOptions()
    {
        WriteConcernConfigurator::configure($this);
    }
}