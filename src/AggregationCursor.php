<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Cursor;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class AggregationCursor implements CursorInterface
{
    /**
     * @var Cursor
     */
    private $mongoCursor;

    /**
     * @var
     */
    private $useCursor;

    /**
     * @var array
     */
    private $typeMap;

    /**
     * @param Cursor $mongoCursor
     * @param bool $useCursor
     * @param array $typeMap
     */
    public function __construct(Cursor $mongoCursor, $useCursor, array $typeMap = [])
    {
        $this->mongoCursor = $mongoCursor;
        $this->useCursor = (bool)$useCursor;
        $this->typeMap = $typeMap;
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        if (true === $this->useCursor) {
            if ($this->typeMap) {
                $this->mongoCursor->setTypeMap($this->typeMap);
            } else {
                $this->mongoCursor->setTypeMap(TypeMapOptions::getDefaults());
            }

            return self::getGenerator($this->mongoCursor);
        }

        $this->mongoCursor->setTypeMap(TypeMapOptions::getDefaults());

        $resultDocument = current($this->mongoCursor->toArray());
        if (!isset($resultDocument['result']) || !is_array($resultDocument['result'])) {
            throw new UnexpectedResultException(
                'Command "aggregate" did not return expected "result" array'
            );
        }

        return self::getGenerator($resultDocument['result']);
    }

    /**
     * @param array|\Traversable $iterable
     * @return \Generator
     */
    private static function getGenerator($iterable)
    {
        foreach ($iterable as $document) {
            yield $document;
        }
    }
}