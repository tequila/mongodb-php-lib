<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\CommandBuilder;

trait CommandBuilderTrait
{
    /**
     * @var CommandBuilder
     */
    private $commandBuilder;

    /**
     * @return CommandBuilder
     */
    public function getCommandBuilder()
    {
        if (null === $this->commandBuilder) {
            $this->commandBuilder = new CommandBuilder();
            $this->commandBuilder->setReadConcern($this->readConcern);
            $this->commandBuilder->setReadPreference($this->readPreference);
            $this->commandBuilder->setWriteConcern($this->writeConcern);
        }

        return $this->commandBuilder;
    }
}