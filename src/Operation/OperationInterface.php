<?php

namespace Tequila\MongoDB\Operation;

use MongoDB\Driver\Manager;

interface OperationInterface
{
    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @return mixed
     */
    public function execute(Manager $manager, $databaseName, $collectionName);
}