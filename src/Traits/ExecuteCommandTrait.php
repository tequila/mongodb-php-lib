<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\Cursor;

trait ExecuteCommandTrait
{
    /**
     * @param array $command
     * @param array $options
     * @return Cursor
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
