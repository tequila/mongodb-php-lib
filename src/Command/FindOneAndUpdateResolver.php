<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\Configurator\CollationConfigurator;
use Tequila\MongoDB\Options\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\Options\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class FindOneAndUpdateResolver extends OptionsResolver
{
    use CachedResolverTrait;

    const RETURN_DOCUMENT_BEFORE = 'before';
    const RETURN_DOCUMENT_AFTER = 'after';

    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        DocumentValidationConfigurator::configure($this);
        WriteConcernConfigurator::configure($this);

        $this->setDefined([
            'maxTimeMS',
            'projection',
            'returnDocument',
            'sort',
            'upsert',
        ]);

        $this->setAllowedValues('returnDocument', [
            self::RETURN_DOCUMENT_BEFORE,
            self::RETURN_DOCUMENT_AFTER,
        ]);

        $this->setDefault('returnDocument', self::RETURN_DOCUMENT_BEFORE);
    }

    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);
        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        if (self::RETURN_DOCUMENT_AFTER === $options['returnDocument']) {
            $options['new'] = true;
        }

        unset($options['returnDocument']);
    }
}