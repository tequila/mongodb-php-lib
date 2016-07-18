<?php

namespace Tequilla\MongoDB\Event;

use Tequilla\MongoDB\Connection;
use MongoDB\Driver\Cursor;

class DropDatabaseEvent extends Event
{
    private $connection;
    private $databaseName;
    private $options;
    private $cursor;

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

    public function getCursor()
    {
        return $this->cursor;
    }

    public function setCursor(Cursor $cursor)
    {
        $this->cursor = $cursor;
    }
}
