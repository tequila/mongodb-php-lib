<?php

namespace Tequilla\MongoDB\Tests;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\ReadPreference;
use Tequilla\MongoDB\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    use WrongInternalTypesProviderTrait;

    /**
     * @covers \Tequilla\MongoDB\Database::__construct()
     * @uses Manager
     * @dataProvider getInvalidStringArgs
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage Database name must be a string
     */
    public function testExceptionOnInvalidDatabaseName($databaseName)
    {
        $manager = new Manager();
        new Database($manager, $databaseName);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::__construct()
     * @uses Manager
     * @dataProvider getInvalidDatabaseOptions
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     */
    public function testExceptionOnInvalidOptionsInConstructor(array $options)
    {
        $manager = new Manager();
        new Database($manager, 'tequilla_mongodb_test', $options);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::__construct()
     * @uses Manager
     * @uses ReadConcern
     * @uses WriteConcern
     * @uses ReadPreference
     */
    public function testDatabaseGetsOptionsFromManagerIfNoProvidedInConstructor()
    {
        $manager = new Manager(
            'mongodb://localhost:27017',
            [
                'readPreference' => 'nearest',
                'readConcernLevel' => 'majority',
                'w' => 2,
                'wtimeoutMS' => 1200,
            ]
        );

        $db = $this->getValidDatabaseInstance($manager);

        $this->assertEquals(
            ReadPreference::RP_NEAREST,
            $db->getReadPreference()->getMode()
        );

        $this->assertEquals(
            ReadConcern::MAJORITY,
            $db->getReadConcern()->getlevel()
        );

        $this->assertEquals(
            1200,
            $db->getWriteConcern()->getWtimeout()
        );
    }

    /**
     * @covers \Tequilla\MongoDB\Database::getReadConcern()
     * @uses ReadConcern
     */
    public function testGetReadConcernReturnsValidValue()
    {
        $db = $this->getValidDatabaseInstance();
        $this->assertEquals(ReadConcern::class, get_class($db->getReadConcern()));
    }

    /**
     * @covers \Tequilla\MongoDB\Database::getWriteConcern()
     * @uses WriteConcern
     */
    public function testGetWriteConcernReturnsValidValue()
    {
        $db = $this->getValidDatabaseInstance();
        $this->assertEquals(WriteConcern::class, get_class($db->getWriteConcern()));
    }

    /**
     * @covers \Tequilla\MongoDB\Database::getReadPreference()
     * @uses ReadPreference
     */
    public function testGetReadPreferenceReturnsValidValue()
    {
        $db = $this->getValidDatabaseInstance();
        $this->assertEquals(ReadPreference::class, get_class($db->getReadPreference()));
    }

    /**
     * @covers \Tequilla\MongoDB\Database::createCollection()
     * @dataProvider getInvalidStringArgs
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage Collection name must be a string
     */
    public function testCreateCollectionThrowsExceptionWhenNameIsNotString($collectionName)
    {
        $db = $this->getValidDatabaseInstance();
        $db->createCollection($collectionName);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::createCollection()
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     */
    public function testCreateCollectionThrowsExceptionOnInvalidOptionsNames()
    {
        $db = $this->getValidDatabaseInstance();
        $db->createCollection('tequilla_mongodb_test', [
            'foo',
            'bar',
            'baz',
        ]);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::createCollection()
     * @dataProvider getInvalidCreateCollectionOptions
     * @expectedException \Tequilla\MongoDB\Exception\InvalidArgumentException
     */
    public function testCreateCollectionThrowsExceptionOnInvalidOptionsTypes(array $options)
    {
        $db = $this->getValidDatabaseInstance();
        $db->createCollection('tequilla_mongodb_test', $options);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::createCollection()
     */
    public function testCreateCollectionReturnsValidResponse()
    {
        $db = $this->getValidDatabaseInstance();
        $result = $db->createCollection('tequilla_mongodb_create_collection_test_' . uniqid());

        $this->assertEquals(1.0, $result[0]['ok']);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::createCollection()
     * @uses Manager
     */
    public function testCreateCollectionCreatesCappedCollection()
    {
        $manager = new Manager();
        $dbName = 'tequilla_mongodb_test_' . uniqid();
        $collectionName = 'tequilla_mongodb_create_collection_test_capped_' . uniqid();
        $db = new Database($manager, $dbName);
        $db->createCollection(
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

        $this->assertEquals($collectionName, $cursor->toArray()[0]['name']);
    }

    /**
     * @covers \Tequilla\MongoDB\Database::__construct()
     * @covers \Tequilla\MongoDB\Database::getName()
     * @uses Manager
     */
    public function testGetNameReturnsNameSpecifiedInConstructor()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $db = new Database($manager, $dbName);

        $this->assertEquals($dbName, $db->getName());
    }

    /**
     * @covers \Tequilla\MongoDB\Database::listCollections()
     * @uses Manager
     */
    public function testListCollectionsReturnsValidResponse()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'tequilla_mongodb_test_list_collections_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $db = new Database($manager, $dbName);
        $result = $db->listCollections();

        foreach ($result as $collectionInfo) {
            if ($collectionInfo['name'] === $collectionName) {
                return;
            }
        }

        throw new \LogicException(
            sprintf(
                'Failed assert that %s::listCollections() returns all collection names in db. Method returned: "%s"',
                Database::class,
                var_export($result, true)
            )
        );
    }

    /**
     * @covers \Tequilla\MongoDB\Database::drop()
     * @uses Manager
     */
    public function testDatabaseDropMethodDropsDatabase()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'tequilla_mongodb_test_database_drop_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $database = new Database($manager, $dbName);
        $database->drop();

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
                    sprintf('Failed assert that %s::drop() drops database', Database::class)
                );
            }
        }
    }

    /**
     * @covers \Tequilla\MongoDB\Database::dropCollection()
     * @uses Manager
     */
    public function testDatabaseDropCollectionMethodDropsCollection()
    {
        $manager = new Manager();
        $dbName = 'tequilla_database_test';
        $collectionName = 'tequilla_mongodb_test_database_drop_collection_' . uniqid();
        $createCommand = new Command(['create' => $collectionName]);
        $manager->executeCommand($dbName, $createCommand);

        $database = new Database($manager, $dbName);
        $database->dropCollection($collectionName);

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
                    sprintf('Failed assert that %s::dropCollection() drops database', Database::class)
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

    /**
     * @param Manager $manager
     * @return \Tequilla\MongoDB\Database
     */
    private function getValidDatabaseInstance(Manager $manager = null)
    {
        if (null === $manager) {
            $manager = new Manager();
        }

        return new Database($manager, 'tequilla_mongodb_test');
    }
}
