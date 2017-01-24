<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\CursorInterface;

trait ExecuteCommandTrait
{
    /**
     * @param array $command
     * @param array $options
     * @return CursorInterface
     */
    private function executeCommand(array $command, array $options)
    {
        return $this
            ->getCommandExecutor()
            ->executeCommand(
                $this->manager,
                $this->databaseName,
                $command,
                $options
            );
    }
}