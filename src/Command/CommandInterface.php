<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;

interface CommandInterface
{
    /**
     * @param Manager $manager
     * @return \MongoDB\Driver\Cursor
     */
    public function execute(Manager $manager);
}