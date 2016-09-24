<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Connection;

class CommandBuilder
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
     * @param Connection $connection
     * @param string $databaseName
     */
    public function __construct(Connection $connection, $databaseName)
    {
        $this->databaseName = (string) $databaseName;
        $this->connection = $connection;
    }

    /**
     * @param string $commandClass
     * @return CommandWrapper
     */
    public function buildCommand($commandClass)
    {
        return new CommandWrapper($this->connection, $this->databaseName, $commandClass);
    }
}