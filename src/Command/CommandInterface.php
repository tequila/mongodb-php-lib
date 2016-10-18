<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\CursorInterface;

interface CommandInterface
{
    /**
     * @param Manager $manager
     * @return CursorInterface
     */
    public function execute(Manager $manager);
}