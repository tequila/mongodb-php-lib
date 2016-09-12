<?php

namespace Tequilla\MongoDB\Event;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class IndexesEvent
 * @package Tequilla\MongoDB\Event
 */
class IndexesEvent extends Event
{
    /**
     * @var \Tequilla\MongoDB\Index[]
     */
    private $indexes;

    /**
     * @var array
     */
    private $commandResult;

    /**
     * IndexesEvent constructor.
     * @param \Tequilla\MongoDB\Index[] $indexes
     */
    public function __construct(array $indexes)
    {
        $this->indexes = $indexes;
    }

    /**
     * @return \Tequilla\MongoDB\Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return array
     */
    public function getCommandResult()
    {
        return $this->commandResult;
    }

    /**
     * @param array $result
     */
    public function setCommandResult(array $result)
    {
        $this->commandResult = $result;
    }
}