<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\DocumentValidationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class FindOneAndUpdateResolver extends OptionsResolver
{
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