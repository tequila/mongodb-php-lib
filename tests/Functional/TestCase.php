<?php

namespace Tequila\MongoDB\Tests\Functional;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        self::dropDatabase();
    }

    public static function tearDownAfterClass()
    {
        self::dropDatabase();
    }

    protected static function dropDatabase()
    {
        self::getManager()->executeCommand(
            self::getDatabaseName(),
            new Command(['dropDatabase' => 1]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );
    }

    protected static function ensureNamespaceExists()
    {
        // Insert document for database to be created if not exists
        $bulk = new BulkWrite();
        $bulk->insert(['foo' => 'bar']);
        self::getManager()->executeBulkWrite(self::getNamespace(), $bulk);
    }

    protected static function getManager()
    {
        return new Manager('mongodb://127.0.0.1/');
    }

    protected static function getDatabaseName()
    {
        return sprintf(
            'tequila_mongodb_tests_%s',
            strtolower((new \ReflectionClass(static::class))->getShortName())
        );
    }

    protected static function getCollectionName()
    {
        return strtolower((new \ReflectionClass(static::class))->getShortName());
    }

    protected static function getNamespace()
    {
        return sprintf('%s.%s', self::getDatabaseName(), self::getCollectionName());
    }
}