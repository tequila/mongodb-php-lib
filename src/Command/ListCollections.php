<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\ListCollectionsOptions;

class ListCollections implements CommandInterface
{
    use Traits\SelectPrimaryServerTrait;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param array $options
     */
    public function __construct($databaseName, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->options = ListCollectionsOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['listCollections' => 1] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}