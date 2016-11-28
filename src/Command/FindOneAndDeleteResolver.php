<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\Configurator\CollationConfigurator;
use Tequila\MongoDB\Options\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class FindOneAndDeleteResolver extends OptionsResolver
{
    use CachedResolverTrait;

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