<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class ListIndexes implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     */
    public function __construct($collectionName)
    {
        $this->options = ['listIndexes' => (string)$collectionName];
    }

    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->options;
    }
}