<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\CommandCursor;

class ListDatabases implements CommandInterface
{
    use Traits\SelectPrimaryServerTrait;

    public function execute(Manager $manager)
    {
        $options = [
            'listDatabases' => 1,
            'typeMap' => [
                'root' => 'array',
                'document' => 'array',
                'array' => 'array',
            ],
        ];

        return new CommandCursor($this->selectPrimaryServer($manager), 'admin', $options);
    }
}