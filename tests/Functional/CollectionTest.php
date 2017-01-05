<?php

namespace Tequila\MongoDB\Functional;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\DatabaseAndCollectionNamesTrait;
use Tequila\MongoDB\Tests\Traits\DropCollectionTrait;
use Tequila\MongoDB\Tests\Traits\EnsureNamespaceExistsTrait;
use Tequila\MongoDB\Tests\Traits\GetManagerTrait;
use Tequila\MongoDB\Write\Model\DeleteMany;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\Write\Result\DeleteResult;
use Tequila\MongoDB\WriteResult;

class CollectionTest extends TestCase
{
    use DatabaseAndCollectionNamesTrait;
    use DropCollectionTrait;
    use EnsureNamespaceExistsTrait;
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

        $result = $this->getCollection()->bulkWrite($requests, ['ordered' => true]);
        $this->assertInstanceOf(WriteResult::class, $result);
        $this->assertSame(true, $result->isAcknowledged());
        $expectedInsertedIdsIndexes = [0, 2, 5, 6, 7, 8];
        $actualInsertedIdsIndexes = array_keys($result->getInsertedIds());
        $this->assertSame($expectedInsertedIdsIndexes, $actualInsertedIdsIndexes);
        $this->assertSame(4, $result->getMatchedCount());
        $this->assertSame(4, $result->getModifiedCount());
        $this->assertSame(6, $result->getInsertedCount());
        $this->assertSame(3, $result->getDeletedCount());
        $this->assertSame(0, $result->getUpsertedCount());
        $this->assertSame([], $result->getUpsertedIds());
        $this->assertNull($result->getWriteConcernError());
        $this->assertSame([], $result->getWriteErrors());
        $this->assertInstanceOf(Server::class, $result->getServer());

        $documents = $this->findAllDocuments();

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

    /**
     * @covers Collection::createIndex()
     */
    public function testCreateIndex()
    {
        $this->dropCollection();

        $indexName = $this->getCollection()->createIndex(['foo' => 1, 'bar' => -1]);

        $indexMatched = false;

        foreach ($this->listIndexes() as $indexInfo) {
            if (
                'foo_1_bar_-1' === $indexInfo->name
                && 1 === $indexInfo->key->foo
                && -1 === $indexInfo->key->bar
            ) {
                $indexMatched = true;
            }
        }

        if (!$indexMatched) {
            throw new \RuntimeException('Failed assert that Collection::createIndex() creates index.');
        }

        $this->assertSame('foo_1_bar_-1', $indexName);
    }

    /**
     * @covers Collection::createIndexes()
     */
    public function testCreateIndexes()
    {
        $this->dropCollection();

        $indexes = [
            new Index(['foo' => 1, 'bar' => -1]),
            new Index(['baz' => -1], ['unique' => true, 'sparse' => true]),
        ];

        $indexNames = $this->getCollection()->createIndexes($indexes);

        $firstIndexMatched = false;
        $secondIndexMatched = false;

        foreach ($this->listIndexes() as $indexInfo) {
            if (
                'foo_1_bar_-1' === $indexInfo->name
                && 1 === $indexInfo->key->foo
                && -1 === $indexInfo->key->bar
            ) {
                $firstIndexMatched = true;

                continue;
            }

            if (
                'baz_-1' === $indexInfo->name
                && -1 === $indexInfo->key->baz
                && true === $indexInfo->sparse
                && true === $indexInfo->unique
            ) {
                $secondIndexMatched = true;

                continue;
            }
        }

        if (!$firstIndexMatched || !$secondIndexMatched) {
            throw new \RuntimeException('Failed assert that Collection::createIndexes() creates indexes.');
        }

        $this->assertSame(['foo_1_bar_-1', 'baz_-1'], $indexNames);
    }

    /**
     * @covers Collection::deleteMany()
     */
    public function testDeleteMany()
    {
        $this->dropCollection();

        $this->bulkInsert([
            ['genre' => 'blues'],
            ['genre' => 'rock'],
            ['genre' => 'jazz'],
            ['genre' => 'trans'],
            ['genre' => 'folk'],
            ['genre' => 'russian rap', 'shouldNotExist' => true],
            ['genre' => 'russian chanson', 'shouldNotExist' => true],
        ]);

        $result = $this->getCollection()->deleteMany(['shouldNotExist' => true]);
        $this->assertInstanceOf(DeleteResult::class, $result);
        $this->assertSame(2, $result->getDeletedCount());

        $documents = $this->findAllDocuments();

        $expected = [
            'blues',
            'rock',
            'jazz',
            'trans',
            'folk',
        ];

        $genres = array_column($documents, 'genre');
        $this->assertSame($expected, $genres);
    }

    /**
     * @covers Collection::deleteOne()
     */
    public function testDeleteOne()
    {
        $this->dropCollection();

        $this->bulkInsert([
            ['drink' => 'tequila', 'alcohol' => true],
            ['drink' => 'wine', 'alcohol' => true],
            ['drink' => 'beer', 'alcohol' => true],
            ['drink' => 'milk', 'alcohol' => false],
            ['drink' => 'coffee', 'alcohol' => false],
        ]);

        $result = $this->getCollection()->deleteOne(['alcohol' => false]);
        $this->assertInstanceOf(DeleteResult::class, $result);
        $this->assertSame(1, $result->getDeletedCount());

        $documents = $this->findAllDocuments();
        $drinks = array_column($documents, 'drink');
        $expected = ['tequila', 'wine', 'beer', 'coffee'];

        $this->assertSame($expected, $drinks);
    }

    private function getCollection()
    {
        $manager = new Manager();

        return new Collection($manager, $this->getDatabaseName(), $this->getCollectionName());
    }

    private function listIndexes()
    {
        $command = new Command(['listIndexes' => $this->getCollectionName()]);
        $cursor = $this->getManager()->executeCommand(
            $this->getDatabaseName(),
            $command,
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );

        return $cursor->toArray();
    }

    private function bulkInsert(array $documents)
    {
        $bulkWrite = new BulkWrite();

        foreach ($documents as $document) {
            $bulkWrite->insert($document);
        }

        $this->getManager()->executeBulkWrite($this->getNamespace(), $bulkWrite);
    }

    private function findAllDocuments()
    {
        $query = new Query([]);

        $cursor = $this->getManager()->executeQuery($this->getNamespace(), $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }
}