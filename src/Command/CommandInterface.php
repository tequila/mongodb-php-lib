<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\CommandCursor;

interface CommandInterface
{
    /**
     * @param Manager $manager
     * @return \MongoDB\Driver\Cursor
     */
    public function execute(Manager $manager);
}