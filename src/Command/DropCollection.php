<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Util\CompatibilityChecker;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\CommandInterface;

class DropCollection implements CommandInterface
{
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
        return CompatibilityChecker::getInstance(
            $serverInfo,
            $this->options,
            ['writeConcern']
        )->resolve();
    }
}