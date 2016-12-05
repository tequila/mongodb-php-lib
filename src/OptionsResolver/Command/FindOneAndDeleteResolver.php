<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class FindOneAndDeleteResolver extends OptionsResolver
{
    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);

        $this->setDefined([
            'maxTimeMS',
            'projection',
            'sort',
        ]);
    }

    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        return $options;
    }
}