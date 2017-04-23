<?php

namespace Tequila\MongoDB\Tests\Functional;

use Tequila\MongoDB\Client;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Tests\Traits\ListDatabaseNamesTrait;

class ClientTest extends TestCase
{
    use ListDatabaseNamesTrait;

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabase()
    {
        self::ensureNamespaceExists();

        $databaseName = static::getDatabaseName();
        $this->getClient()->dropDatabase($databaseName);

        $this->assertNotContains($databaseName, $this->listDatabaseNames());
    }

    /**
     * @covers Client::listDatabases()
     */
    public function testListDatabases()
    {
        self::ensureNamespaceExists();

        $databases = $this->getClient()->listDatabases();

        $databaseNames = array_column($databases, 'name');
        $expectedDatabaseNames = $this->listDatabaseNames();

        $this->assertEquals($expectedDatabaseNames, $databaseNames);
        $this->assertContains(self::getDatabaseName(), $databaseNames);
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectCollection()
    {
        $client = $this->getClient();
        $collection = $client->selectCollection(self::getDatabaseName(), self::getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(self::getCollectionName(), $collection->getCollectionName());
        $this->assertEquals(self::getDatabaseName(), $collection->getDatabaseName());
    }

    /**
     * @covers Client::selectDatabase()
     */
    public function testSelectDatabase()
    {
        $client = $this->getClient();
        $database = $client->selectDatabase(self::getDatabaseName());
        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals(self::getDatabaseName(), $database->getDatabaseName());
    }

    private function getClient()
    {
        return new Client();
    }
}
