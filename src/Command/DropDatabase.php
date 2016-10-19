<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\WritingCommandOptions;

class DropDatabase implements CommandInterface
{
    use Traits\PrimaryServerTrait;

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
        $this->options = WritingCommandOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = ['dropDatabase' => 1] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}