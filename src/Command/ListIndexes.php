<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Traits\SelectPrimaryServerTrait;
use Tequila\MongoDB\CommandCursor;

class ListIndexes implements CommandInterface
{
    use SelectPrimaryServerTrait;

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

        return new CommandCursor(
            $this->selectPrimaryServer($manager),
            $this->databaseName,
            $options
        );
    }
}