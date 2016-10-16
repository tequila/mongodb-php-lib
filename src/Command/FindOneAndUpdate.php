<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\FindOneAndUpdateOptions;

class FindOneAndUpdate implements CommandInterface
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
     * @var array|object
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $filter
     * @param $update
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $filter, $update, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->filter = $filter;
        $this->update = $update;
        $this->options = ['update' => $this->update] + FindOneAndUpdateOptions::resolve($options);
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