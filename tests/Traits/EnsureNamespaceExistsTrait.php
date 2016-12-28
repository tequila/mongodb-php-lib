<?php

namespace Tequila\MongoDB\Tests\Traits;

use MongoDB\Driver\BulkWrite;

trait EnsureNamespaceExistsTrait
{
    private function ensureNamespaceExists()
    {
        // Insert document for database to be created if not exists
        $bulk = new BulkWrite();
        $bulk->insert(['foo' => 'bar']);
        $this->getManager()->executeBulkWrite($this->getNamespace(), $bulk);
    }
}