<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\Server;

class CompatibilityResolver
{
    /**
     * @var Server
     */
    private $server;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Server $server
     * @param array $options
     */
    public function __construct(Server $server, array $options)
    {
        $this->server = $server;
        $this->options = $options;
    }

    /**
     * @param Server $server
     * @param array $options
     * @return static
     */
    public static function getInstance(Server $server, array $options)
    {
        return new static($server, $options);
    }

    /**
     * @return array
     */
    public function resolve()
    {
        return $this->options;
    }

    /**
     * Checks whether the server supports document validation. Throws an UnsupportedException otherwise
     * @throws UnsupportedException
     * @return $this
     */
    public function checkDocumentValidation()
    {
        if (array_key_exists('bypassDocumentValidation', $this->options) && !$this->server->supportsDocumentValidation()) {
            throw new UnsupportedException(
                'Option "bypassDocumentValidation" is not supported by the server'
            );
        }

        return $this;
    }

    /**
     * Checks whether the server supports "collation" option. Throws an UnsupportedException otherwise
     * @throws UnsupportedException
     * @return $this
     */
    public function checkCollation()
    {
        if (array_key_exists('collation', $this->options) && !$this->server->supportsCollation()) {
            throw new UnsupportedException('Option "collation" is not supported by the server');
        }

        return $this;
    }

    /**
     * Checks whether the server supports "readConcern" option. Throws an UnsupportedException otherwise
     * @throws UnsupportedException
     * @return $this
     */
    public function checkReadConcern()
    {
        if (
            array_key_exists('readConcern', $this->options)
            && !$this->server->supportsReadConcern()
            && !in_array($this->options['readConcern']->getLevel(), [ReadConcern::LOCAL, null], true)
        ) {
            throw new UnsupportedException(
                'Option "readConcern" is not supported by the server'
            );
        }

        return $this;
    }

    /**
     * Checks whether the server supports "writeConcern" option. Throws an UnsupportedException otherwise
     * @return $this
     */
    public function checkWriteConcern()
    {
        if (array_key_exists('writeConcern', $this->options) && !$this->server->supportsWriteConcern()) {
            unset($this->options['writeConcern']);
        }

        return $this;
    }
}