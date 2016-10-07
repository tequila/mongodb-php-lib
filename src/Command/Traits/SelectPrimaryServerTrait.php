<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;

trait SelectPrimaryServerTrait
{
    /**
     * @param Manager $manager
     * @return \MongoDB\Driver\Server
     */
    private function executeOnPrimaryServer(Manager $manager, $databaseName, array $options)
    {
        $server = $manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $server->executeCommand($databaseName, new Command($options));
    }
}