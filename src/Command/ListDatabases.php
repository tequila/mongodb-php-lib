<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;

class ListDatabases implements CommandInterface
{
    use Traits\PrimaryServerTrait;

    public function execute(Manager $manager)
    {
        $options = [
            'listDatabases' => 1,
        ];

        return $this->executeOnPrimaryServer($manager, 'admin', $options);
    }
}