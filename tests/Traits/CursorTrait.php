<?php

namespace Tequila\MongoDB\Tests\Traits;

use Tequila\MongoDB\Cursor;

trait CursorTrait
{
    /**
     * @var Cursor
     */
    private $cursor;

    /**
     * @return Cursor
     */
    public function getCursor()
    {
        if (null === $this->cursor) {
            $this->cursor = $this->prophesize(Cursor::class)->reveal();
        }

        return $this->cursor;
    }
}
