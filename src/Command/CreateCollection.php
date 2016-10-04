<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\CreateCollectionOptions;
use Tequila\MongoDB\CommandCursor;

class CreateCollection implements CommandInterface
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
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->options = CreateCollectionOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['create' => $this->collectionName] + $this->options;

        return new CommandCursor($this->selectPrimaryServer($manager), $this->databaseName, $options);
    }
}