<?php

namespace Tequila\MongoDB\Functional;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\EnsureNamespaceExistsTrait;
use Tequila\MongoDB\Tests\Traits\GetManagerTrait;
use Tequila\MongoDB\Tests\Traits\ListDatabaseNamesTrait;

class ClientTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;
    use EnsureNamespaceExistsTrait;
    use GetManagerTrait;
    use ListDatabaseNamesTrait;

    /**
     * @covers Client::dropDatabase()
     */
    public function testDropDatabase()
    {
        $this->ensureNamespaceExists();

        $databaseName = $this->getDatabaseName();
        $this->getClient()->dropDatabase($databaseName);

        $this->assertNotContains($databaseName, $this->listDatabaseNames());
    }

    /**
     * @covers Client::listDatabases()
     */
    public function testListDatabases()
    {
        $this->ensureNamespaceExists();

        $databases = $this->getClient()->listDatabases();

        $databaseNames = array_map(function(array $databaseInfo) {
            return $databaseInfo['name'];
        }, $databases);

        $expectedDatabaseNames = $this->listDatabaseNames();

        $this->assertEquals($expectedDatabaseNames, $databaseNames);
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