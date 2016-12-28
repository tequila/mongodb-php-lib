<?php

namespace Tequila\MongoDB\Functional;

use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\ReadPreference;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;

class DatabaseTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;

    public function testCreateCollection()
    {
        $manager = new \MongoDB\Driver\Manager('mongodb://127.0.0.1/');

        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $dropCollectionCommand = new Command(['drop' => $this->getCollectionName()]);
        try {
            $manager->executeCommand($this->getDatabaseName(), $dropCollectionCommand, $readPreference);
        } catch(MongoDBRuntimeException $e) {
            if('ns not found' !== $e->getMessage()) {
                throw $e;
            }
        }

        $database = $this->getDatabase();
        $database->createCollection($this->getCollectionName());

        $listCollectionsCommand = new Command(['listCollections' => 1]);
        $cursor = $manager->executeCommand($this->getDatabaseName(), $listCollectionsCommand, $readPreference);
        $collectionNames = array_map(function(\stdClass $collectionInfo) {
            return $collectionInfo->name;
        }, $cursor->toArray());

        $this->assertContains($this->getCollectionName(), $collectionNames);
    }

    private function getDatabase()
    {
        $manager = new Manager();

        return new Database($manager, $this->getDatabaseName());
    }
}