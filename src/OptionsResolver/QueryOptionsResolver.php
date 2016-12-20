<?php

namespace Tequila\MongoDB\OptionsResolver;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\Configurator\CollationConfigurator;
use Tequila\MongoDB\QueryInterface;

class QueryOptionsResolver extends OptionsResolver
{
    public function configureOptions()
    {
        CollationConfigurator::configure($this);

        $this->setDefined([
            'allowPartialResults',
            'awaitData',
            'batchSize',
            'comment',
            'cursorType',
            'exhaust',
            'limit',
            'maxTimeMS',
            'modifiers',
            'noCursorTimeout',
            'oplogReplay',
            'projection',
            'readConcern',
            'readPreference',
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
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('modifiers', $documentTypes)
            ->setAllowedTypes('noCursorTimeout', 'bool')
            ->setAllowedTypes('oplogReplay', 'bool')
            ->setAllowedTypes('projection', $documentTypes)
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
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