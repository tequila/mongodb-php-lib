<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\CommandExecutor;

trait CommandExecutorTrait
{
    /**
     * @var CommandExecutor
     */
    private $commandExecutor;

    /**
     * @return CommandExecutor
     */
    private function getCommandExecutor()
    {
        if (null === $this->commandExecutor) {
            $this->commandExecutor = new CommandExecutor(
                $this->readConcern,
                $this->readPreference,
                $this->writeConcern
            );
        }

        return $this->commandExecutor;
    }
}
