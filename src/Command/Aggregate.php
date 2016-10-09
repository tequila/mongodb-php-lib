<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Command\Options\AggregateOptions;

class Aggregate implements CommandInterface
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
    private $pipeline;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $pipeline
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $pipeline, array $options = [])
    {
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->pipeline = $pipeline;
        $this->options = AggregateOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        if (isset($this->options['readConcern'])) {
            /** @var ReadConcern $readConcern */
            $readConcern = $this->options['readConcern'];
            if ($this->hasOutStage() && ReadConcern::MAJORITY === $readConcern->getLevel()) {
                unset($this->options['readConcern']);
            } else {
                $this->options['readConcern'] = ['level' => $readConcern->getLevel()];
            }
        }

        if ($this->hasOutStage()) {
            $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        } else {
            if (isset($this->options['readPreference'])) {
                $readPreference = $this->options['readPreference'];
            } else {
                $readPreference = null;
            }
        }

        unset($this->options['readPreference']);

        $options = ['aggregate' => $this->collectionName, 'pipeline' => $this->pipeline];
        $options += $this->options;
        $command = new Command($options);

        return $manager->executeCommand($this->databaseName, $command, $readPreference);
    }

    private function hasOutStage()
    {
        $lastStage = end($this->pipeline);

        return '$out' === key($lastStage);
    }
}