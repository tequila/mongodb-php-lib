<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\TypeMapConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class FindOneAndDeleteResolver extends OptionsResolver
{
    public function resolve(array $options = [])
    {
        $options = parent::resolve($options);
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        return $options;
    }

    protected function configureOptions()
    {
        CollationConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        TypeMapConfigurator::configure($this);

        $this->setDefined([
            'projection',
            'sort',
        ]);
    }
}
