<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\CompatibilityResolver;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class DropCollection implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param array $options
     */
    public function __construct($collectionName, array $options = [])
    {
        $this->options = ['drop' => (string)$collectionName] + WritingCommandOptions::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return CompatibilityResolver::getInstance(
            $serverInfo,
            $this->options,
            ['writeConcern']
        )->resolve();
    }
}