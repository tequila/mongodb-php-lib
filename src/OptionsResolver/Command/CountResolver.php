<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\Index;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class CountResolver extends OptionsResolver
{
    protected function configureOptions()
    {
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);

        $this->setDefined([
            'limit',
            'skip',
            'hint',
        ]);

        $this
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('hint', ['string', 'array', 'object']);

        $this->setNormalizer('hint', function($value) {
            if (is_array($value) || is_object($value)) {
                $value = Index::generateIndexName((array)$value);
            }

            return $value;
        });
    }
}