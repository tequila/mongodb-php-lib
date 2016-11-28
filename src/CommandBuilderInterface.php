<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;

interface CommandBuilderInterface
{
    /**
     * @param ReadConcern $readConcern
     */
    public function setReadConcern(ReadConcern $readConcern);

    /**
     * @param ReadPreference $readPreference
     */
    public function setReadPreference(ReadPreference $readPreference);

    /**
     * @param WriteConcern $writeConcern
     */
    public function setWriteConcern(WriteConcern $writeConcern);

    /**
     * @param array $command
     * @param array $options
     * @param string $resolverClass
     * @return CompiledCommandInterface
     */
    public function createCommand(array $command, array $options, $resolverClass);
}