<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class DropIndexes implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param string $indexName
     * @param array $options
     */
    public function __construct($collectionName, $indexName, array $options = [])
    {
        $this->options = [
                'dropIndexes' => (string)$collectionName,
                'index' => $indexName
            ] + WritingCommandOptions::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }
}