<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\CommonOptions;
use Tequila\MongoDB\CommandCursor;

class DropDatabase implements CommandInterface
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
        $this->databaseName = (string)$databaseName;
        $this->options = CommonOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['dropDatabase' => 1] + $this->options;

        return new CommandCursor($this->selectPrimaryServer($manager), $this->databaseName, $options);
    }
}