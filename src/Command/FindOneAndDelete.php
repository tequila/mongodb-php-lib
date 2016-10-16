<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\FindOneAndDeleteOptions;

class FindOneAndDelete implements CommandInterface
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
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $filter
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $filter, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter = $filter;
        $this->options = FindOneAndDeleteOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $findAndModify = new FindAndModify(
            $this->databaseName,
            $this->collectionName,
            $this->filter,
            $this->options
        );

        return $findAndModify->execute($manager);
    }
}