<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Cursor;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class CommandCursor implements CursorInterface
{
    /**
     * @var Cursor
     */
    private $mongoCursor;

    /**
     * CommandCursor constructor.
     * @param Cursor $mongoCursor
     */
    public function __construct(Cursor $mongoCursor)
    {
        $this->mongoCursor = $mongoCursor;
    }

    public function getIterator()
    {
        $this->mongoCursor->setTypeMap(TypeMapOptions::getDefaults());

        foreach ($this->mongoCursor as $document) {
            yield $document;
        }
    }
}