<?php

namespace Tequila\MongoDB\Tests\Functional;

use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\ListCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\ListDatabaseNamesTrait;

class DatabaseTest extends TestCase
{
    use ListCollectionNamesTrait;
    use ListDatabaseNamesTrait;

    /**
     * @covers Database::createCollection()
     */
    public function testCreateCollection()
    {
        self::dropDatabase();

        $this->getDatabase()->createCollection(self::getCollectionName());

        $this->assertContains(self::getCollectionName(), $this->listCollectionNames());
    }

    /**
     * @covers Database::drop()
     */
    public function testDrop()
    {
        self::ensureNamespaceExists();

        $this->getDatabase()->drop();

        $this->assertNotContains(self::getDatabaseName(), $this->listDatabaseNames());
    }

    /**
     * @covers Database::dropCollection()
     */
    public function testDropCollection()
    {
        self::ensureNamespaceExists();

        $this->getDatabase()->dropCollection(self::getCollectionName());

        $this->assertNotContains(self::getCollectionName(), $this->listCollectionNames());
    }

    /**
     * @covers Database::listCollections()
     */
    public function testListCollections()
    {
        self::ensureNamespaceExists();

        $collections = $this->getDatabase()->listCollections();
        $collectionNames = array_column(iterator_to_array($collections), 'name');
        $expectedCollectionNames = $this->listCollectionNames();

        $this->assertEquals($expectedCollectionNames, $collectionNames);
        $this->assertContains(self::getCollectionName(), $collectionNames);
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectCollection()
    {
        $collection = $this->getDatabase()->selectCollection(self::getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(self::getCollectionName(), $collection->getCollectionName());
        $this->assertEquals(self::getDatabaseName(), $collection->getDatabaseName());
    }

    private function getDatabase()
    {
        $manager = new Manager();

        return new Database($manager, $this->getDatabaseName());
    }
}