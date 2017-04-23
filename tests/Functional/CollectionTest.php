<?php

namespace Tequila\MongoDB\Tests\Functional;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\Tests\Traits\ListCollectionNamesTrait;
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
    use ListCollectionNamesTrait;

    public function setUp()
    {
        $this->dropCollection();
    }

    /**
     * @covers Collection::aggregate()
     */
    public function testAggregate()
    {
        // populate collection with test data
        $this->bulkInsert([
            ['integerField' => 10],
            ['integerField' => 20],
            ['integerField' => 30],
            ['integerField' => 40],
        ]);

        $pipeline = [
            [
                '$group' => [
                    '_id' => null,
                    'sum' => ['$sum' => '$integerField'],
                ],
            ],
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
        $this->assertTrue($result->isAcknowledged());
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
        $this->bulkInsert([
            ['foo' => 'bar'],
            ['bar' => 'baz'],
            ['spam' => true],
            ['willBeCounted' => true],
            ['willBeCounted' => 'yes'],
            ['willBeCounted' => 'whatever'],
        ]);

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

    /**
     * @covers Collection::distinct()
     */
    public function testDistinct()
    {
        $this->bulkInsert([
            ['drink' => 'tequila', 'country' => 'Mexico'],
            ['drink' => 'whisky', 'country' => 'Scotland'],
            ['drink' => 'whiskey', 'country' => 'Ireland'],
            ['drink' => 'vodka', 'country' => 'Russia'],
            ['drink' => 'tequila', 'country' => 'Mexico'],
        ]);

        $drinks = $this->getCollection()->distinct('drink');
        $expectedDrinks = ['tequila', 'whisky', 'whiskey', 'vodka'];

        $this->assertSame($expectedDrinks, $drinks);
    }

    /**
     * @covers Collection::drop()
     */
    public function testDrop()
    {
        self::ensureNamespaceExists();

        $this->getCollection()->drop();

        $this->assertNotContains(static::getCollectionName(), $this->listCollectionNames());
    }

    /**
     * @covers Collection::dropIndexes()
     */
    public function testDropIndexes()
    {
        $this->createIndexes();

        $result = $this->getCollection()->dropIndexes();
        $this->assertSame(1.0, $result['ok']);

        $indexNames = array_column($this->listIndexes(), 'name');
        $this->assertSame(['_id_'], $indexNames);
    }

    /**
     * @covers Collection::dropIndex()
     */
    public function testDropIndex()
    {
        $this->createIndexes();

        $result = $this->getCollection()->dropIndex('baz_-1');
        $this->assertSame(1.0, $result['ok']);

        $indexNames = array_column($this->listIndexes(), 'name');
        $expectedIndexNames = ['_id_', 'foo_1_bar_1', 'nullable_-1'];
        $this->assertSame($expectedIndexNames, $indexNames);
    }

    /**
     * @covers Collection::find()
     */
    public function testFind()
    {
        $this->bulkInsert([
            ['country' => 'Ukraine', 'region' => 'Europe'],
            ['country' => 'Great Britain', 'region' => 'Europe'],
            ['country' => 'China', 'region' => 'Asia'],
            ['country' => 'Thailand', 'region' => 'Asia'],
            ['country' => 'France', 'region' => 'Europe'],
            ['country' => 'USA', 'region' => 'North America'],
            ['country' => 'Switzerland', 'region' => 'Europe'],
            ['country' => 'Germany', 'region' => 'Europe'],
            ['country' => 'Austria', 'region' => 'Europe'],
        ]);

        $cursor = $this->getCollection()->find(['region' => 'Europe'], ['limit' => 5]);
        $countries = array_column($cursor->toArray(), 'country');
        $expectedCountries = ['Ukraine', 'Great Britain', 'France', 'Switzerland', 'Germany'];

        $this->assertSame($expectedCountries, $countries);
    }

    /**
     * @covers Collection::findOne()
     */
    public function testFindOne()
    {
        $this->bulkInsert([
            ['city' => 'New York'],
            ['city' => 'Miami'],
            ['city' => 'Kiev'],
            ['city' => 'Paris'],
        ]);

        $document = $this->getCollection()->findOne();
        $this->assertSame('New York', $document['city']);
    }

    /**
     * @covers Collection::findOne()
     */
    public function testFindOneReturnsNullWhenNoDocumentsMatch()
    {
        $this->bulkInsert([
            ['city' => 'New York'],
            ['city' => 'Miami'],
            ['city' => 'Kiev'],
            ['city' => 'Paris'],
        ]);

        $document = $this->getCollection()->findOne(['foo' => 'bar']);
        $this->assertNull($document);
    }

    /**
     * @covers Collection::findOneAndDelete()
     */
    public function testFindOneAndDelete()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $document = $this->getCollection()->findOneAndDelete(['foo' => 'bar']);
        $this->assertSame('bar', $document['foo']);

        $cursor = self::getManager()->executeQuery(self::getNamespace(), new Query(['foo' => 'bar']));
        $this->assertFalse(current($cursor->toArray()));
    }

    /**
     * @covers Collection::findOneAndReplace()
     */
    public function testFindOneAndReplace()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $oldDocument = $this->getCollection()->findOneAndReplace(
            ['foo' => 'bar'],
            ['bar' => 'baz', 'spam' => 'egg']
        );

        $this->assertSame('bar', $oldDocument['foo']);

        $cursor = self::getManager()->executeQuery(self::getNamespace(), new Query(['foo' => 'bar']));
        $this->assertFalse(current($cursor->toArray()));

        $cursor = self::getManager()->executeQuery(self::getNamespace(), new Query(['bar' => 'baz']));
        $newDocument = current($cursor->toArray());
        $this->assertSame('egg', $newDocument->spam);
    }

    /**
     * @covers Collection::findOneAndReplace()
     */
    public function testFindOneAndReplaceReturnsOldDocumentWhenOptionReturnDocumentBefore()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $oldDocument = $this->getCollection()->findOneAndReplace(
            ['foo' => 'bar'],
            ['bar' => 'baz', 'spam' => 'egg'],
            ['returnDocument' => 'before']
        );

        $this->assertSame('bar', $oldDocument['foo']);
    }

    /**
     * @covers Collection::findOneAndReplace()
     */
    public function testFindOneAndReplaceReturnsNewDocumentWhenOptionReturnDocumentAfter()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $oldDocument = $this->getCollection()->findOneAndReplace(
            ['foo' => 'bar'],
            ['bar' => 'baz', 'spam' => 'egg'],
            ['returnDocument' => 'after']
        );

        $this->assertSame('egg', $oldDocument['spam']);
    }

    /**
     * @covers Collection::findOneAndUpdate()
     */
    public function testFindOneAndUpdate()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $oldDocument = $this->getCollection()->findOneAndUpdate(
            ['foo' => 'bar'],
            ['$set' => ['spam' => 'egg'], '$inc' => ['timesUpdated' => 1]]
        );

        $this->assertSame('bar', $oldDocument['foo']);
        $this->assertArrayNotHasKey('spam', $oldDocument);

        $cursor = self::getManager()->executeQuery(
            self::getNamespace(),
            new Query(['timesUpdated' => 1])
        );
        $newDocument = current($cursor->toArray());
        $this->assertSame('egg', $newDocument->spam);
        $this->assertSame(1, $newDocument->timesUpdated);
    }

    /**
     * @covers Collection::findOneAndUpdate()
     */
    public function testFindOneAndUpdateReturnsOldDocumentWhenOptionReturnDocumentBefore()
    {
        $this->bulkInsert([['foo' => 'bar', 'timesUpdated' => 0]]);

        $oldDocument = $this->getCollection()->findOneAndUpdate(
            ['foo' => 'bar'],
            ['$set' => ['spam' => 'egg'], '$inc' => ['timesUpdated' => 1]],
            ['returnDocument' => 'before']
        );

        $this->assertSame('bar', $oldDocument['foo']);
        $this->assertArrayNotHasKey('spam', $oldDocument);
    }

    /**
     * @covers Collection::findOneAndUpdate()
     */
    public function testFindOneAndUpdateReturnsOldDocumentWhenOptionReturnDocumentAfter()
    {
        $this->bulkInsert([['foo' => 'bar', 'timesUpdated' => 0]]);

        $newDocument = $this->getCollection()->findOneAndUpdate(
            ['foo' => 'bar'],
            ['$set' => ['spam' => 'egg'], '$inc' => ['timesUpdated' => 1]],
            ['returnDocument' => 'after']
        );

        $this->assertSame('bar', $newDocument['foo']);
        $this->assertSame('egg', $newDocument['spam']);
        $this->assertSame(1, $newDocument['timesUpdated']);
    }

    /**
     * @covers Collection::getCollectionName()
     */
    public function testGetCollectionName()
    {
        $this->assertSame(static::getCollectionName(), $this->getCollection()->getCollectionName());
    }

    /**
     * @covers Collection::getDatabaseName()
     */
    public function testGetDatabaseName()
    {
        $this->assertSame(static::getDatabaseName(), $this->getCollection()->getDatabaseName());
    }

    /**
     * @covers Collection::getNamespace()
     */
    public function testGetNamespace()
    {
        $this->assertSame(static::getNamespace(), $this->getCollection()->getNamespace());
    }

    /**
     * @covers Collection::insertMany()
     */
    public function testInsertMany()
    {
        $documents = [
            ['foo' => 'bar'],
            ['bar' => 'baz'],
            ['spam' => 'egg'],
        ];

        $result = $this->getCollection()->insertMany($documents);
        $this->assertTrue($result->isAcknowledged());
        $this->assertSame(3, $result->getInsertedCount());
        $this->assertSame([0, 1, 2], array_keys($result->getInsertedIds()));
        $this->assertNull($result->getWriteConcernError());
        $this->assertSame([], $result->getWriteErrors());

        $foundDocuments = $this->findAllDocuments();
        $this->assertSame('bar', $foundDocuments[0]['foo']);
        $this->assertSame('baz', $foundDocuments[1]['bar']);
        $this->assertSame('egg', $foundDocuments[2]['spam']);
    }

    /**
     * @covers Collection::insertOne()
     */
    public function testInsertOne()
    {
        $result = $this->getCollection()->insertOne(['foo' => 'bar']);
        $insertedId = $result->getInsertedId();
        $this->assertTrue($result->isAcknowledged());
        $this->assertInstanceOf(ObjectID::class, $insertedId);
        $this->assertNull($result->getWriteConcernError());
        $this->assertSame([], $result->getWriteErrors());

        $documents = $this->findAllDocuments();
        $this->assertCount(1, $documents);
        $this->assertSame((string) $insertedId, (string) $documents[0]['_id']);
        $this->assertSame('bar', $documents[0]['foo']);
    }

    /**
     * @covers Collection::listIndexes()
     */
    public function testListIndexes()
    {
        $this->createIndexes();

        $indexes = $this->getCollection()->listIndexes();
        $indexNames = array_column($indexes, 'name');
        $this->assertSame(['_id_', 'foo_1_bar_1', 'baz_-1', 'nullable_-1'], $indexNames);
        $this->assertTrue($indexes[2]['unique']);
        $this->assertTrue($indexes[3]['sparse']);
    }

    /**
     * @covers Collection::replaceOne()
     */
    public function testReplaceOne()
    {
        $this->bulkInsert([['foo' => 'bar']]);

        $this->getCollection()->replaceOne(['foo' => 'bar'], ['bar' => 'baz']);

        $documents = $this->findAllDocuments();
        $this->assertSame('baz', $documents[0]['bar']);
        $this->assertArrayNotHasKey('foo', $documents[0]);
    }

    /**
     * @covers Collection::updateMany()
     */
    public function testUpdateMany()
    {
        $this->bulkInsert([
            ['city' => 'New York', 'population' => 8500000],
            ['city' => 'Kiev', 'population' => 3000000],
            ['city' => 'Miami', 'population' => 400000],
        ]);

        $this->getCollection()->updateMany(
            ['population' => ['$gt' => 1000000]],
            ['$set' => ['megacity' => true]]
        );

        $cursor = self::getManager()->executeQuery(
            self::getNamespace(),
            new Query(['megacity' => true]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );

        $cityNames = array_column($cursor->toArray(), 'city');
        $this->assertSame(['New York', 'Kiev'], $cityNames);
    }

    public function testUpdateOne()
    {
        $this->bulkInsert([
            ['city' => 'New York', 'population' => 8500000],
            ['city' => 'Kiev', 'population' => 3000000],
            ['city' => 'Miami', 'population' => 400000],
        ]);

        $this->getCollection()->updateOne(
            ['population' => ['$gt' => 1000000]],
            ['$set' => ['megacity' => true]]
        );

        $cursor = self::getManager()->executeQuery(
            self::getNamespace(),
            new Query(['megacity' => true]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );

        $cityNames = array_column($cursor->toArray(), 'city');
        $this->assertSame(['New York'], $cityNames);
    }

    /**
     * @return Collection
     */
    private function getCollection()
    {
        $manager = new Manager();

        return new Collection(
            $manager,
            self::getDatabaseName(),
            self::getCollectionName(),
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );
    }

    private function listIndexes()
    {
        $command = new Command(['listIndexes' => self::getCollectionName()]);
        $cursor = static::getManager()->executeCommand(
            static::getDatabaseName(),
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

        self::getManager()->executeBulkWrite(self::getNamespace(), $bulkWrite);
    }

    private function findAllDocuments()
    {
        $query = new Query([]);

        $cursor = self::getManager()->executeQuery(self::getNamespace(), $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    private function createIndexes()
    {
        $indexes = [
            ['key' => ['foo' => 1, 'bar' => 1], 'name' => 'foo_1_bar_1'],
            ['key' => ['baz' => -1], 'name' => 'baz_-1', 'unique' => true],
            ['key' => ['nullable' => -1], 'name' => 'nullable_-1', 'sparse' => true],
        ];

        $command = new Command(
            ['createIndexes' => self::getCollectionName(), 'indexes' => $indexes]
        );

        self::getManager()->executeCommand(self::getDatabaseName(), $command);
    }

    private function dropCollection()
    {
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        $dropCollectionCommand = new Command(['drop' => self::getCollectionName()]);
        try {
            self::getManager()->executeCommand(self::getDatabaseName(), $dropCollectionCommand, $readPreference);
        } catch (MongoDBRuntimeException $e) {
            if ('ns not found' !== $e->getMessage()) {
                throw $e;
            }
        }
    }
}
