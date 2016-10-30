<?php

namespace Tequila\MongoDB\Tests\Traits;

use Tequila\MongoDB\CursorInterface;

trait CursorTrait
{
    /**
     * @var CursorInterface
     */
    private $cursor;

    /**
     * @return CursorInterface
     */
    public function getCursor()
    {
        if (null === $this->cursor) {
            $this->cursor = $this->prophesize(CursorInterface::class)->reveal();
        }

        return $this->cursor;
    }
}