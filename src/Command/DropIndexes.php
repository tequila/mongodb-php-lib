<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\CommonOptions;
use Tequila\MongoDB\CommandCursor;

class DropIndexes implements CommandInterface
{
    use Traits\SelectPrimaryServerTrait;

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
        $this->options = CommonOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['dropIndexes' => $this->collectionName, 'index' => $this->indexName] + $this->options;

        return new CommandCursor(
            $this->selectPrimaryServer($manager),
            $this->databaseName,
            $options
        );
    }
}