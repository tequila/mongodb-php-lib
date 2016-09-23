<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Cursor;
use Tequila\MongoDB\Exception\LogicException;

class CommandCursor
{
    /**
     * @var Cursor
     */
    private $mongoCursor;

    /**
     * @var array
     */
    private $arrayRepresentation;

    /**
     * @param Cursor $cursor
     */
    public function __construct(Cursor $cursor)
    {
        $this->mongoCursor = $cursor;
    }

    /**
     * @return \MongoDB\Driver\Server
     */
    public function getServer()
    {
        return $this->mongoCursor->getServer();
    }

    /**
     * @return Cursor
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (null === $this->arrayRepresentation) {
            if ($this->mongoCursor->isDead()) {
                throw new LogicException('Attempt to get array representation from dead cursor');
            }

            $this->arrayRepresentation = $this->mongoCursor->toArray();
        }

        return $this->arrayRepresentation;
    }
}