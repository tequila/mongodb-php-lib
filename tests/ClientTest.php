<?php

namespace Tequila\MongoDB\Tests;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Client;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Tests\Traits\GetDatabaseAndCollectionNamesTrait;

class ClientTest extends TestCase
{
    use GetDatabaseAndCollectionNamesTrait;

    /**
     * @covers Client::__construct()
     */
    public function testConstructorWithoutArguments()
    {
        return new Client();
    }

    /**
     * @depends testConstructorWithoutArguments
     * @covers  Client::dropDatabase()
     *
     * @param Client $client
     */
    public function testDropDatabaseDropsDatabase(Client $client)
    {
        $result = $client->dropDatabase($this->getDatabaseName());
        $this->assertEquals(1.0, $result['ok']);
    }

    /**
     * @covers  Client::listDatabases()
     * @depends testConstructorWithoutArguments
     *
     * @param Client $client
     */
    public function testListDatabases(Client $client)
    {
        $manager = new Manager();
        $createCollectionCommand = new Command(['create' => $this->getCollectionName()]);
        $manager->executeCommand(
            $this->getDatabaseName(),
            $createCollectionCommand,
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );

        $result = $client->listDatabases();
        foreach ($result as $dbInfo) {
            if ($dbInfo->getName() === $this->getDatabaseName()) {
                return;
            }
        }

        throw new \LogicException('Method Client::listDatabases() did not return expected database');
    }

    /**
     * @covers  Client::selectCollection()
     * @depends testConstructorWithoutArguments
     *
     * @param Client $client
     * @return Collection
     */
    public function testSelectCollectionWithDefaultOptions(Client $client)
    {
        $collectionName = $this->getCollectionName();
        $collection = $client->selectCollection($this->getDatabaseName(), $collectionName);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($this->getDatabaseName(), $collection->getDatabaseName());
        $this->assertEquals($collectionName, $collection->getCollectionName());

        return $collection;
    }

    /**
     * @covers  Client::selectDatabase()
     * @depends testConstructorWithoutArguments
     *
     * @param Client $client
     * @return Database
     */
    public function testSelectDatabaseWithDefaultOptions(Client $client)
    {
        $database = $client->selectDatabase($this->getDatabaseName());

        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals($this->getDatabaseName(), $database->getDatabaseName());

        return $database;
    }
}