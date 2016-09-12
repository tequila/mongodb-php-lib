<?php

namespace Tequilla\MongoDB\Event;

use Symfony\Component\EventDispatcher\Event;
use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\DatabaseInterface;

/**
 * Class GetDatabaseEvent
 * @package Tequilla\MongoDB\Event
 */
class GetDatabaseEvent extends Event
{
    private $connection;
    private $databaseName;
    private $options;
    private $database;

    public function __construct(Connection $connection, $databaseName, array $options = [])
    {
        $this->connection = $connection;
        $this->databaseName = $databaseName;
        $this->options = $options;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function setDatabase(DatabaseInterface $database)
    {
        $this->database = $database;
    }
}
