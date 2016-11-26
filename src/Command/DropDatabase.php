<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Util\CompatibilityChecker;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class DropDatabase implements CommandInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = ['dropDatabase' => 1] + WritingCommandOptions::resolve($options);
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