<?php

namespace Tequilla\MongoDB\Tests;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\ReadPreference;
use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\Database;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    use WrongInternalTypesProviderTrait;

    /**
     * @covers \Tequilla\MongoDB\Connection::createCollection()
     * @dataProvider getInvalidStringArgs
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage Collection name must be a string
     */
    public function testCreateCollectionThrowsExceptionWhenNameIsNotString($collectionName)
    {
        $connection = new Connection();
        $connection->createCollection('tequilla_mongodb_test', $collectionName);
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::createCollection()
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     */
    public function testCreateCollectionThrowsExceptionOnInvalidOptionsNames()
    {
        $connection = new Connection();
        $connection->createCollection(
            'tequilla_mongodb_test',
            'test_create_collection_' . uniqid(),
            [
                'foo',
                'bar',
                'baz',
            ]
        );
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::createCollection()
     * @dataProvider getInvalidCreateCollectionOptions
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     */
    public function testCreateCollectionThrowsExceptionOnInvalidOptionsTypes(array $options)
    {
        $connection = new Connection();
        $connection->createCollection(
            'tequilla_mongodb_test',
            'test_create_collection_' . uniqid(),
            $options
        );
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::createCollection()
     */
    public function testCreateCollectionReturnsValidResponse()
    {
        $connection = new Connection();
        $result = $connection->createCollection(
            'tequilla_mongodb_test',
            'test_create_collection_' . uniqid()
        );

        $this->assertEquals(1.0, $result[0]['ok']);
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::createCollection()
     * @uses Manager
     */
    public function testCreateCollectionCreatesCappedCollection()
    {
        $dbName = 'tequilla_mongodb_test';
        $collectionName = 'test_create_capped_collection_' . uniqid();
        $connection = new Connection();
        $connection->createCollection(
            $dbName,
            $collectionName,
            [
                'capped' => true,
                'size' => 10000,
            ]
        );

        $manager = new Manager();
        $command = new Command([
            'listCollections' => 1,
            'filter' => ['options.capped' => true],
        ]);
        $cursor = $manager->executeCommand($dbName, $command);
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        $collections = $cursor->toArray();
        foreach ($collections as $collectionInfo) {
            if ($collectionInfo['name'] === $collectionName) {
                return; //test passed
            }
        }

        throw new \LogicException('Failed assert that Connection::createCollection can create capped collection');
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::listCollections()
     * @uses Manager
     */
    public function testListCollectionsReturnsValidResponse()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'test_list_collections_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $connection = new Connection();
        $result = $connection->listCollections($dbName);

        foreach ($result as $collectionInfo) {
            if ($collectionInfo['name'] === $collectionName) {
                return; // test passed
            }
        }

        throw new \LogicException(
            sprintf(
                'Failed assert that %s::listCollections() returns all collection names in database. Method returned: "%s"',
                Database::class,
                print_r($result, true)
            )
        );
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::dropDatabase()
     * @uses Manager
     */
    public function testDropDatabaseMethodDropsDatabase()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'test_drop_database_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $connection = new Connection();
        $connection->dropDatabase($dbName);

        $listDatabasesCommand = new Command(['listDatabases' => 1]);
        $cursor = $manager->executeCommand('admin', $listDatabasesCommand);
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        $result = $cursor->toArray();

        foreach ($result[0]['databases'] as $dbInfo) {
            if ($dbInfo['name'] === $dbName) {
                throw new \LogicException(
                    'Failed assert that Connection::dropDatabase() method drops database'
                );
            }
        }
    }

    /**
     * @covers \Tequilla\MongoDB\Connection::dropCollection()
     * @uses Manager
     */
    public function testDropCollectionMethodDropsCollection()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'test_drop_collection_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $connection = new Connection();
        $connection->dropCollection($dbName, $collectionName);

        $listCollectionsCommand = new Command(['listCollections' => 1]);
        $cursor = $manager->executeCommand($dbName, $listCollectionsCommand);
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);
        $collections = $cursor->toArray();

        foreach ($collections as $collectionInfo) {
            if ($collectionInfo['name'] === $collectionName) {
                throw new \LogicException(
                    'Failed assert that Connection::dropCollection() drops collection'
                );
            }
        }
    }

    public function getInvalidCreateCollectionOptions()
    {
        $args = [];

        // Exception must be thrown if "size" option is not provided
        $args[] = [['capped' => true]];

        // Exception must be thrown if "capped" option is not set to true
        $args[] = [['size' => 10000]];
        $args[] = [['size' => 10000, 'capped' => false]];
        $args[] = [['max' => 10000]];
        $args[] = [['max' => 10000, 'capped' => false]];

        foreach (self::getInvalidBooleanValues() as $value) {
            $args[] = [['capped' => $value, 'size' => 10000]];
        }

        foreach (self::getInvalidIntegerValues() as $value) {
            $args[] = [['capped' => true, 'size' => $value]];
            $args[] = [['capped' => true, 'size' => 10000, 'max' => $value]];
        }

        foreach (self::getInvalidIntegerValues() as $value) {
            $args[] = [['flags' => $value]];
        }

        return $args;
    }

    public function getInvalidDatabaseOptions()
    {
        $args = [];
        $invalidOptions = ['invalid', true, null, 2.0, []];

        $args[] = [['unexistedOption' => 1]];
        foreach (['readConcern', 'writeConcern', 'readPreference'] as $validOptionName) {
            foreach ($invalidOptions as $invalidOptionValue) {
                $args[] = [[$validOptionName => $invalidOptionValue]];
            }
        }

        $args[] = [['typeMap' => 'stringValue']];

        return $args;
    }
}
