<?php

namespace Tequila\MongoDB\Operation;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\Driver\DriverOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Util\TypeUtil;

class Find
{
    const CURSOR_TYPE_NON_TAILABLE = 1;
    const CURSOR_TYPE_TAILABLE = 2;
    const CURSOR_TYPE_TAILABLE_AWAIT = 3;

    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var array
     */
    private $typeMap;

    /**
     * @param array|object $filter
     * @param array $options
     */
    public function __construct($filter, array $options = [])
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$filter must be an array or an object, %s given',
                    TypeUtil::getType($filter)
                )
            );
        }

        $this->filter = $filter;
        $this->options = $this->resolve($options);
    }

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @return \MongoDB\Driver\Cursor
     */
    public function execute(Manager $manager, $databaseName, $collectionName)
    {
        $query = new Query($this->filter, $this->options);

        $cursor = $manager->executeQuery(
            $databaseName . '.' . $collectionName,
            $query,
            $this->readPreference
        );
        $cursor->setTypeMap($this->typeMap);

        return $cursor;
    }

    /**
     * @param array $options
     * @return array
     */
    private function resolve(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $this->readPreference = isset($this->options['readPreference']) ? $this->options['readPreference'] : null;
        unset($options['readPreference']);

        $this->typeMap = $options['typeMap'];
        unset($options['typeMap']);

        if (isset($options['allowPartialResults']) && true === $options['allowPartialResults']) {
            $options['partial'] = true;
            unset($options['allowPartialResults']);
        }

        if (isset($options['cursorType'])) {
            $cursorType = $options['cursorType'];

            if (in_array($cursorType, [self::CURSOR_TYPE_TAILABLE, self::CURSOR_TYPE_TAILABLE_AWAIT], true)) {
                $options['tailable'] = true;
            }

            if (self::CURSOR_TYPE_TAILABLE_AWAIT === $cursorType) {
                $options['awaitData'] = true;
            }

            unset($options['cursorType']);
        }

        if (isset($options['comment'])) {
            $options['modifiers']['$comment'] = $options['comment'];
            unset($options['comment']);
        }

        if (isset($options['maxTimeMS'])) {
            $options['modifiers']['$maxTimeMS'] = $options['maxTimeMS'];
            unset($options['maxTimeMS']);
        }

        if(empty($options['modifiers'])) {
            unset($options['modifiers']);
        }

        return $options;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        DriverOptions::configureOptions($resolver);

        $resolver->setDefined([
            'allowPartialResults',
            'awaitData',
            'batchSize',
            'collation', // for MongoDB 3.4 and higher
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

        $resolver
            ->setAllowedTypes('allowPartialResults', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('collation', $documentTypes)
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

        $resolver->setAllowedValues('cursorType', [
            self::CURSOR_TYPE_NON_TAILABLE,
            self::CURSOR_TYPE_TAILABLE,
            self::CURSOR_TYPE_TAILABLE_AWAIT,
        ]);

        $resolver->setDefault('modifiers', []);
    }
}