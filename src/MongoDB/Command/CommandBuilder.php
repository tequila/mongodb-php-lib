<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\Manager;

/**
 * Class CommandBuilder
 * @package Tequilla\MongoDB\Command
 */
class CommandBuilder
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * CommandBuilder constructor.
     * @param Manager $manager
     * @param $databaseName
     */
    public function __construct(Manager $manager, $databaseName)
    {
        $this->databaseName = (string)$databaseName;
        $this->manager = $manager;
    }

    /**
     * @param $commandClass
     * @return CommandWrapper
     */
    public function buildCommand($commandClass)
    {
        return new CommandWrapper($this->manager, $this->databaseName, $commandClass);
    }
}