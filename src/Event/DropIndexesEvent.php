<?php

namespace Tequilla\Event;

use Symfony\Component\EventDispatcher\Event;

class DropIndexesEvent extends Event
{
    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var string
     */
    private $indexName;

    /**
     * DropIndexesEvent constructor.
     * @param string $databaseName
     * @param string $collectionName
     * @param string $indexName
     */
    public function __construct($databaseName, $collectionName, $indexName)
    {
        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->indexName = (string) $collectionName;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }
}