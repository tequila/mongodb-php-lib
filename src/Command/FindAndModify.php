<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\FindAndModifyOptions;

class FindAndModify implements CommandInterface
{
    use Traits\PrimaryServerTrait;

    const RETURN_DOCUMENT_BEFORE = 'before';
    const RETURN_DOCUMENT_AFTER  = 'after';

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
        $options = ['findAndModify' => $this->collectionName, 'query' => $this->query] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}