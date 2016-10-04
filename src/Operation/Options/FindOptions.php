<?php

namespace Tequila\MongoDB\Operation\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class FindOptions implements OptionsInterface
{
    use CachedResolverTrait {
        CachedResolverTrait::resolve as resolveOptions;
    }

    const CURSOR_TYPE_NON_TAILABLE = 1;
    const CURSOR_TYPE_TAILABLE = 2;
    const CURSOR_TYPE_TAILABLE_AWAIT = 3;

    /**
     * @param array $options
     * @return array
     */
    public static function resolve(array $options)
    {
        $options = self::resolveOptions($options);

        if (!empty($options['allowPartialResults'])) {
            $options['partial'] = true;
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
            unset($options['comment']);
        }

        if(empty($options['modifiers'])) {
            unset($options['modifiers']);
        }

        return $options;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        TypeMapOptions::configureOptions($resolver);
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
            'partial',
            'projection',
            'readConcern',
            'readPreference',
            'skip',
            'sort',
            'tailable',
            'typeMap',
        ]);

        $resolver
            ->setAllowedTypes('allowPartialResults', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('collation', 'string')
            ->setAllowedTypes('comment', 'string')
            ->setAllowedValues('cursorType', [
                self::CURSOR_TYPE_NON_TAILABLE,
                self::CURSOR_TYPE_TAILABLE,
                self::CURSOR_TYPE_TAILABLE_AWAIT,
            ])
            ->setAllowedTypes('exhaust', 'bool')
            ->setAllowedTypes('limit', 'integer')
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('modifiers', ['array', 'object'])
            ->setDefault('modifiers', [])
            ->setAllowedTypes('noCursorTimeout', 'bool')
            ->setAllowedTypes('oplogReplay', 'bool')
            ->setAllowedTypes('projection', ['array', 'object'])
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class)
            ->setAllowedTypes('skip', 'integer')
            ->setAllowedTypes('sort', ['array', 'object'])
            ->setAllowedTypes('typeMap', 'array');
    }
}