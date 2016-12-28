<?php

namespace Tequila\MongoDB\Functional;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\DropCollectionTrait;
use Tequila\MongoDB\Tests\Traits\EnsureNamespaceExistsTrait;
use Tequila\MongoDB\Tests\Traits\GetManagerTrait;
use Tequila\MongoDB\Tests\Traits\ListCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\ListDatabaseNamesTrait;

class DatabaseTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;
    use DropCollectionTrait;
    use EnsureNamespaceExistsTrait;
    use GetManagerTrait;
    use ListCollectionNamesTrait;
    use ListDatabaseNamesTrait;

    /**
     * @covers Database::createCollection()
     */
    public function testCreateCollection()
    {
        $this->dropCollection();

        $this->getDatabase()->createCollection($this->getCollectionName());

        $this->assertContains($this->getCollectionName(), $this->listCollectionNames());
    }

    /**
     * @covers Database::drop()
     */
    public function testDrop()
    {
        $this->ensureNamespaceExists();

        $this->getDatabase()->drop();

        $this->assertNotContains($this->getDatabaseName(), $this->listDatabaseNames());
    }

    /**
     * @covers Database::dropCollection()
     */
    public function testDropCollection()
    {
        $this->ensureNamespaceExists();

        $this->getDatabase()->dropCollection($this->getCollectionName());

        $this->assertNotContains($this->getCollectionName(), $this->listCollectionNames());
    }

    /**
     * @covers Database::listCollections()
     */
    public function testListCollections()
    {
        $this->ensureNamespaceExists();

        $collections = $this->getDatabase()->listCollections();
        $collectionNames = array_map(function(array $collectionInfo) {
            return $collectionInfo['name'];
        }, iterator_to_array($collections));

        $expectedCollectionNames = $this->listCollectionNames();

        $this->assertEquals($expectedCollectionNames, $collectionNames);
        $this->assertContains($this->getCollectionName(), $collectionNames);
    }

    /**
     * @covers Client::selectCollection()
     */
    public function testSelectCollection()
    {
        $collection = $this->getDatabase()->selectCollection($this->getCollectionName());
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals($this->getCollectionName(), $collection->getCollectionName());
        $this->assertEquals($this->getDatabaseName(), $collection->getDatabaseName());
    }

    private function getDatabase()
    {
        $manager = new Manager();

        return new Database($manager, $this->getDatabaseName());
    }
}