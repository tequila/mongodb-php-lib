<?php

namespace Tequilla\MongoDB\Event;

use Symfony\Component\EventDispatcher\Event;
use Tequilla\MongoDB\DatabaseInterface;

class DatabaseEvent extends Event
{
    /**
     * @var DatabaseInterface
     */
    private $database;

    /**
     * DatabaseEvent constructor.
     * @param DatabaseInterface $database
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @return DatabaseInterface
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
