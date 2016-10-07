<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;

class ListIndexes implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @param string $databaseName
     * @param string $collectionName
     */
    public function __construct($databaseName, $collectionName)
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
    }

    public function execute(Manager $manager)
    {
        $options = ['listIndexes' => $this->collectionName];

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}