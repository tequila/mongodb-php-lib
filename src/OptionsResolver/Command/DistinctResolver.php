<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\CommandOptions;
use Tequila\MongoDB\OptionsResolver\Command\Traits\ReadConcernTrait;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DistinctResolver extends OptionsResolver implements ReadConcernAwareInterface, CompatibilityResolverInterface
{
    use ReadConcernTrait;

    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);
    }

    public function resolveCompatibilities(CommandOptions $options)
    {
        $options
            ->resolveCollation()
            ->resolveReadConcern($this->readConcern);
    }
}