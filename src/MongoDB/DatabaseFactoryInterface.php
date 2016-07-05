<?php

namespace Tequilla\MongoDB;

interface DatabaseFactoryInterface extends ManagerAwareInterface
{
    /**
     * @param string $name
     * @param array $options
     * @return DatabaseInterface
     */
    public function createDatabaseInstance($name, array $options = []);
}