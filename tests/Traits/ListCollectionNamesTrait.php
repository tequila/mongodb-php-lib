<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\Command;
use MongoDB\Driver\ReadPreference;

trait ListCollectionNamesTrait
{
    private function listCollectionNames()
    {
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $listCollectionsCommand = new Command(['listCollections' => 1]);
        $cursor = static::getManager()->executeCommand(static::getDatabaseName(), $listCollectionsCommand, $readPreference);

        return array_column($cursor->toArray(), 'name');
    }
}
