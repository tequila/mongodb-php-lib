<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\RuntimeException;
use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\Server;

class ServerCompatibleOptions extends \ArrayObject
{
    /**
     * @var Server
     */
    private $server;

    /**
     * Checks whether the server supports document validation.
     * Throws an UnsupportedException if it does not, and the option "bypassDocumentValidation" is set
     * @throws UnsupportedException
     * @return $this
     */
    public function resolveDocumentValidation()
    {
        if (isset($this['bypassDocumentValidation'])) {
            if (!$this->getServer()->supportsDocumentValidation()) {
                throw new UnsupportedException(
                    'Option "bypassDocumentValidation" is not supported by the server'
                );
            }
        }

        return $this;
    }

    /**
     * Checks whether the server supports "collation" option.
     * Throws an UnsupportedException if it does not and "collation" option is set
     * @throws UnsupportedException
     * @return $this
     */
    public function resolveCollation()
    {
        if (isset($this['collation']) && !$this->server->supportsCollation()) {
            throw new UnsupportedException('Option "collation" is not supported by the server');
        }

        return $this;
    }

    /**
     * Checks whether the server supports "readConcern" option for this command.
     * Throws an UnsupportedException if it does not, and "readConcern" option is set.
     * @param ReadConcern|null $defaultValue
     * @return $this
     */
    public function resolveReadConcern(ReadConcern $defaultValue = null)
    {
        $server = $this->getServer();

        if (
            !isset($this['readConcern'])
            && $server->supportsReadConcern()
            && $defaultValue
            && null !== $defaultValue->getLevel()
        ) {
            $this['readConcern'] = $defaultValue;
        }

        if (isset($this['readConcern']) && null === $this['readConcern']->getLevel()) {
            unset($this['readConcern']);
        }

        if (isset($this['readConcern']) && !$server->supportsReadConcern()) {
            throw new UnsupportedException(
                'Option "readConcern" is not supported by the server'
            );
        }

        return $this;
    }

    /**
     * Checks whether the server supports "writeConcern" option.
     * Throws an UnsupportedException if it does not, and "writeConcern" option is set
     * @param WriteConcern|null $defaultValue
     * @return $this
     */
    public function resolveWriteConcern(WriteConcern $defaultValue = null)
    {
        if (!isset($this['writeConcern']) && $this->getServer()->supportsWriteConcern() && $defaultValue) {
            $this['writeConcern'] = $defaultValue;
        }

        if (isset($this['writeConcern']) && !$this->getServer()->supportsWriteConcern()) {
            unset($this['writeConcern']);
        }

        // Write concern in "findAndModify" command is a special case and requires a different wire version
        if (
            'findAndModify' === key($this->getArrayCopy())
            && isset($this['writeConcern'])
            && !$this->getServer()->supportsWireVersion(4)
        ) {
            unset($this['writeConcern']);
        }

        return $this;
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @return Server
     */
    private function getServer()
    {
        if (null === $this->server) {
            throw new RuntimeException('Server was not set on this instance');
        }

        return $this->server;
    }
}