<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;

class DatabaseFactory implements DatabaseFactoryInterface
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param string $name
     * @param array $options
     * @return Database
     */
    public function createDatabaseInstance($name, array $options = [])
    {
        return new Database($this->manager, $name, $options);
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }
}