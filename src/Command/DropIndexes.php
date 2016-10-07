<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;

class DropIndexes implements CommandInterface
{
    use Traits\PrimaryServerTrait;

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
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param string $indexName
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, $indexName, array $options = [])
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->indexName = (string)$indexName;
        $this->options = $options;
    }

    public function execute(Manager $manager)
    {
        $options = ['dropIndexes' => $this->collectionName, 'index' => $this->indexName] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}