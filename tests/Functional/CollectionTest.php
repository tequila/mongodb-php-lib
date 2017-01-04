<?php

namespace Tequila\MongoDB\Functional;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\DropCollectionTrait;
use Tequila\MongoDB\Tests\Traits\GetManagerTrait;
use Tequila\MongoDB\Write\Model\DeleteMany;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;

class CollectionTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;
    use DropCollectionTrait;
    use GetManagerTrait;

    /**
     * @covers Collection::aggregate()
     */
    public function testAggregate()
    {
        $this->dropCollection();

        // populate collection with test data
        $bulk = new BulkWrite();
        $bulk->insert(['integerField' => 10]);
        $bulk->insert(['integerField' => 20]);
        $bulk->insert(['integerField' => 30]);
        $bulk->insert(['integerField' => 40]);
        $this->getManager()->executeBulkWrite($this->getNamespace(), $bulk);

        $pipeline = [
            [
                '$group' => [
                    '_id' => null,
                    'sum' => ['$sum' => '$integerField'],
                ],
            ]
        ];

        $cursor = $this->getCollection()->aggregate($pipeline);
        $firstDocument = $cursor->current();

        $this->assertArrayHasKey('sum', $firstDocument);
        $this->assertEquals(100, $firstDocument['sum']);
    }

    /**
     * @covers Collection::bulkWrite()
     */
    public function testBulkWrite()
    {
        $this->dropCollection();

        $requests = [
            new InsertOne(['replaceMe' => true]),
            new ReplaceOne(['replaceMe' => true], ['isReplacementDocument' => true]),
            new InsertOne(['foo' => 'bar']),
            new UpdateOne(['isReplacementDocument' => true], ['$set' => ['fieldAddedByUpdateOne' => true]]),
            new UpdateMany([], ['$set' => ['fieldAddedByUpdateMany' => 'yes']]),
            new InsertOne(['deleteMe' => 'now']),
            new InsertOne(['deleteMe' => 'now']),
            new InsertOne(['deleteMany' => true]),
            new InsertOne(['deleteMany' => true, 'otherField' => 'something that will be lost']),
            new DeleteOne(['deleteMe' => 'now']),
            new DeleteMany(['deleteMany' => true]),
        ];

        $this->getCollection()->bulkWrite($requests, ['ordered' => true]);

        $cursor = $this
            ->getManager()
            ->executeQuery($this->getNamespace(), new Query([]));

        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);
        $documents = $cursor->toArray();

        $this->assertCount(3, $documents);

        $this->assertCount(4, $documents[0]);
        $this->assertArrayHasKey('_id', $documents[0]);
        $this->assertArrayHasKey('isReplacementDocument', $documents[0]);
        $this->assertArrayHasKey('fieldAddedByUpdateOne', $documents[0]);
        $this->assertArrayHasKey('fieldAddedByUpdateMany', $documents[0]);

        $this->assertCount(3, $documents[1]);
        $this->assertArrayHasKey('_id', $documents[1]);
        $this->assertArrayHasKey('foo', $documents[1]);
        $this->assertArrayHasKey('fieldAddedByUpdateMany', $documents[1]);

        $this->assertCount(2, $documents[2]);
        $this->assertArrayHasKey('_id', $documents[2]);
        $this->assertArrayHasKey('deleteMe', $documents[2]);
    }

    /**
     * @covers Collection::count()
     */
    public function testCount()
    {
        $this->dropCollection();

        $bulk = new BulkWrite();
        $bulk->insert(['foo' => 'bar']);
        $bulk->insert(['bar' => 'baz']);
        $bulk->insert(['spam' => true]);
        $bulk->insert(['willBeCounted' => true]);
        $bulk->insert(['willBeCounted' => 'yes']);
        $bulk->insert(['willBeCounted' => 'whatever']);

        $this->getManager()->executeBulkWrite($this->getNamespace(), $bulk);

        $totalCount = $this->getCollection()->count();
        $count = $this->getCollection()->count(['willBeCounted' => ['$exists' => true]]);

        $this->assertEquals(6, $totalCount);
        $this->assertEquals(3, $count);
    }

    public function testCreateIndex()
    {

    }

    private function getCollection()
    {
        $manager = new Manager();

        return new Collection($manager, $this->getDatabaseName(), $this->getCollectionName());
    }
}