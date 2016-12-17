<?php

namespace Tequila\MongoDB\Traits;

use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\OptionsResolver\TypeMapResolver;

trait ExecuteCommandTrait
{
    /**
     * @param array $command
     * @param array $options
     * @param $resolverClass
     * @return CursorInterface
     */
    private function executeCommand(array $command, array $options, $resolverClass)
    {
        $compiledCommand = $this
            ->getCommandBuilder()
            ->createCommand($command, $options, $resolverClass);

        $cursor = $this->runCommand(
            $compiledCommand,
            $command->getReadPreference()
        );

        $cursor->setTypeMap(TypeMapResolver::getDefault());

        return $cursor;
    }
}