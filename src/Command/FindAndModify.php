<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\FindAndModifyOptions;

class FindAndModify implements CommandInterface
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
     * @var array
     */
    private $query;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $query
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $query, array $options)
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->query = $query;
        $this->options = FindAndModifyOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        return $this->executeOnPrimaryServer($manager, $this->databaseName, $this->options);
    }
}