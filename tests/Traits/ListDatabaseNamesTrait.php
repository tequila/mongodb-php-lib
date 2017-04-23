<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadPreference;

trait ListDatabaseNamesTrait
{
    private function listDatabaseNames()
    {
        // List databases and check that database does not exists
        $listDatabasesCommand = new Command(['listDatabases' => 1]);
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $cursor = static::getManager()->executeCommand('admin', $listDatabasesCommand, $readPreference);
        $result = current($cursor->toArray());

        return array_column($result->databases, 'name');
    }
}
