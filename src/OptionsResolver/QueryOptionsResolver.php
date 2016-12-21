<?php

namespace Tequila\MongoDB\OptionsResolver;

use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\MaxTimeConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\QueryInterface;

class QueryOptionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        CollationConfigurator::configure($this);
        MaxTimeConfigurator::configure($this);
        ReadConcernConfigurator::configure($this);
        ReadPreferenceConfigurator::configure($this);

        $this->setDefined([
            'allowPartialResults',
            'awaitData',
            'batchSize',
            'comment',
            'cursorType',
            'exhaust',
            'limit',
            'modifiers',
            'noCursorTimeout',
            'oplogReplay',
            'projection',
            'skip',
            'sort',
        ]);

        $documentTypes = ['array', 'object'];

        $this
            ->setAllowedTypes('allowPartialResults', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('comment', 'string')
            ->setAllowedTypes('exhaust', 'bool')
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('modifiers', $documentTypes)
            ->setAllowedTypes('noCursorTimeout', 'bool')
            ->setAllowedTypes('oplogReplay', 'bool')
            ->setAllowedTypes('projection', $documentTypes)
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('sort', ['array', 'object']);

        $this->setAllowedValues('cursorType', [
            QueryInterface::CURSOR_NON_TAILABLE,
            QueryInterface::CURSOR_TAILABLE,
            QueryInterface::CURSOR_TAILABLE_AWAIT,
        ]);

        $this->setDefault('modifiers', []);
    }

    public function resolve(array $options = array())
    {
        $options = parent::resolve($options);

        if (isset($options['cursorType'])) {
            $cursorType = $options['cursorType'];

            if (QueryInterface::CURSOR_TAILABLE === $cursorType) {
                $options['tailable'] = true;
            }

            if (QueryInterface::CURSOR_TAILABLE_AWAIT === $cursorType) {
                $options['tailable'] = true;
                $options['awaitData'] = true;
            }

            unset($options['cursorType']);
        }

        $options['modifiers'] = (array)$options['modifiers'];
        if (empty($options['modifiers'])) {
            unset($options['modifiers']);
        }
    }
}