<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

trait PrimaryServerTrait
{
    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    private function executeOnPrimaryServer(Manager $manager, $databaseName, array $options)
    {
        $server = $manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        $cursor = $server->executeCommand($databaseName, new Command($options));
        $cursor->setTypeMap(TypeMapOptions::getDefaults());

        return $cursor;
    }
}