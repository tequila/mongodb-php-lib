<?php

namespace Tequilla\MongoDB\Event;

use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\Exception\InvalidArgumentException;

/**
 * Class DatabaseDroppedEvent
 * @package Tequilla\MongoDB\Event
 */
class DatabaseDroppedEvent extends Event
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
    private $commandResult;

    /**
     * DatabaseDroppedEvent constructor.
     * @param Connection $connection
     * @param string $databaseName
     * @param array $commandResult
     */
    public function __construct(Connection $connection, $databaseName, array $commandResult)
    {
        if (!is_string($databaseName)) {
            throw new InvalidArgumentException('Database name must be a string');
        }
        $this->connection = $connection;
        $this->databaseName = (string) $databaseName;
        $this->commandResult = $commandResult;
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
    public function getCommandResult()
    {
        return $this->commandResult;
    }
}
