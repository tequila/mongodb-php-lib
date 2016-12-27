<?php

namespace Tequila\MongoDB\Functional;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\ReadPreference;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;

class ClientTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabase()
    {
        $databaseName = $this->getDatabaseName();
        $manager = new \MongoDB\Driver\Manager('mongodb://127.0.0.1/');

        // Insert document for database to be created if not exists
        $bulk = new BulkWrite();
        $bulk->insert(['foo' => 'bar']);
        $manager->executeBulkWrite($this->getNamespace(), $bulk);

        $client = $this->getClient();
        $client->dropDatabase($databaseName);

        // List databases and check that database does not exists
        $listDatabasesCommand = new Command(['listDatabases' => 1]);
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $cursor = $manager->executeCommand('admin', $listDatabasesCommand, $readPreference);
        $result = current($cursor->toArray());
        $databaseNames = array_map(function(\stdClass $databaseInfo) {
            return $databaseInfo->name;
        }, $result->databases);

        $this->assertNotContains($databaseName, $databaseNames);
    }

    /**
     * @covers Client::listDatabases()
     */
    public function testListDatabases()
    {
        $manager = new \MongoDB\Driver\Manager('mongodb://127.0.0.1/');

        // Insert document for database to be created if not exists
        $bulk = new BulkWrite();
        $bulk->insert(['foo' => 'bar']);
        $manager->executeBulkWrite($this->getNamespace(), $bulk);
        $client = $this->getClient();
        $databases = $client->listDatabases();

        $databaseNames = array_map(function(array $databaseInfo) {
            return $databaseInfo['name'];
        }, $databases);

        $this->assertContains($this->getDatabaseName(), $databaseNames);
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectCollection()
    {
        $client = $this->getClient();
        $collection = $client->selectCollection($this->getDatabaseName(), $this->getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($this->getCollectionName(), $collection->getCollectionName());
        $this->assertEquals($this->getDatabaseName(), $collection->getDatabaseName());
    }

    /**
     * @covers Client::selectDatabase()
     */
    public function testSelectDatabase()
    {
        $client = $this->getClient();
        $database = $client->selectDatabase($this->getDatabaseName());
        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals($this->getDatabaseName(), $database->getDatabaseName());
    }

    private function getClient()
    {
        $manager = new Manager();

        return new Client($manager);
    }
}