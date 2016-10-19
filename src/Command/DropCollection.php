<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\WritingCommandOptions;

class DropCollection implements CommandInterface
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
        $this->options = WritingCommandOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['drop' => $this->collectionName] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}