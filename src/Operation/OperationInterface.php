<?php

namespace Tequila\MongoDB\Operation;

use Tequila\MongoDB\Connection;

interface OperationInterface
{
    /**
     * @param Connection $connection
     * @param string $databaseName
     * @param string $collectionName
     * @return mixed
     */
    public function execute(Connection $connection, $databaseName, $collectionName);
}