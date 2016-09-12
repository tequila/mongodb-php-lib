<?php

namespace Tequilla\MongoDB\Event;

use Symfony\Component\EventDispatcher\Event;
use Tequilla\MongoDB\CommandCursor;
use Tequilla\MongoDB\Connection;

class DropDatabaseEvent extends Event
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var array
     */
    private $options;

    /**
     * @var CommandCursor
     */
    private $cursor;

    public function __construct(Connection $connection, $databaseName, array $options = [])
    {
        $this->connection = $connection;
        $this->databaseName = (string) $databaseName;
        $this->options = $options;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return CommandCursor
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * @param CommandCursor $cursor
     */
    public function setCursor(CommandCursor $cursor)
    {
        $this->cursor = $cursor;
    }
}
