<?php

namespace Tequila\MongoDB\Tests\Traits;

trait GetDatabaseAndCollectionNamesTrait
{
    protected function getDatabaseName()
    {
        return sprintf(
            'tequila_mongodb_tests_%s',
            strtolower((new \ReflectionObject($this))->getShortName())
        );
    }

    public function getCollectionName()
    {
        return strtolower((new \ReflectionObject($this))->getShortName());
    }

    public function getNamespace()
    {
        return sprintf('%s.%s', $this->getDatabaseName(), $this->getCollectionName());
    }
}