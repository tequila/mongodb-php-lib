<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\CompatibilityChecker;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\TypeMapOptions;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\Util\TypeUtil;

class FindQuery implements QueryInterface
{
    use CachedResolverTrait;

    const CURSOR_TYPE_NON_TAILABLE = 1;
    const CURSOR_TYPE_TAILABLE = 2;
    const CURSOR_TYPE_TAILABLE_AWAIT = 3;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $compiledOptions;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var array
     */
    private $typeMap;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
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
        $this->compileOptions($options);
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return CompatibilityChecker::getInstance(
            $serverInfo,
            $this->compiledOptions,
            ['collation', 'readConcern']
        )->resolve();
    }

    /**
     * @return ReadPreference|null
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    /**
     * @return array
     */
    public function getTypeMap()
    {
        return $this->typeMap;
    }

    /**
     * Validates Find operation input options and compiles them to format,
     * acceptable by the low-level driver
     *
     * @param array $options
     */
    private function compileOptions(array $options)
    {
        // Validate input options
        $options = self::resolve($options);

        // Compile $options to server-acceptable format
        $this->readPreference = isset($options['readPreference']) ? $options['readPreference'] : null;
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

        $this->compiledOptions = $options;
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
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
            'typeMap',
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
            ->setAllowedTypes('sort', ['array', 'object'])
            ->setAllowedTypes('typeMap', 'array');

        $resolver->setAllowedValues('cursorType', [
            self::CURSOR_TYPE_NON_TAILABLE,
            self::CURSOR_TYPE_TAILABLE,
            self::CURSOR_TYPE_TAILABLE_AWAIT,
        ]);

        $resolver
            ->setDefault('modifiers', [])
            ->setDefault('typeMap', function (Options $options) {
                return [];
            });

        $resolver->setNormalizer('typeMap', function(Options $options, $typeMap) {
            return TypeMapOptions::resolve($typeMap);
        });
    }
}