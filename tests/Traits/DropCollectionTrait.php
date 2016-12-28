<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\ReadPreference;

trait DropCollectionTrait
{
    private function dropCollection()
    {
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $dropCollectionCommand = new Command(['drop' => $this->getCollectionName()]);
        try {
            $this->getManager()->executeCommand($this->getDatabaseName(), $dropCollectionCommand, $readPreference);
        } catch(MongoDBRuntimeException $e) {
            if('ns not found' !== $e->getMessage()) {
                throw $e;
            }
        }
    }
}