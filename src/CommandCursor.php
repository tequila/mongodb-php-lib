<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Server;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class CommandCursor implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $arrayRepresentation;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @var Cursor
     */
    protected $mongoCursor;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var array
     */
    protected $typeMap;

    /**
     * @param Server $server
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(Server $server, $databaseName, array $options)
    {
        if (isset($options['typeMap'])) {
            $this->typeMap = $options['typeMap'];
            unset($options['typeMap']);
        } else {
            $this->typeMap = TypeMapOptions::getDefaultTypeMap();
        }

        $this->server = $server;
        $this->databaseName = $databaseName;
        $this->options = $options;

        $this->initMongoCursor();
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        if (!$this->arrayRepresentation) {
            return $this->createGenerator();
        }

        $this->locked = true;

        return new \ArrayIterator($this->arrayRepresentation);
    }

    /**
     * @return \MongoDB\Driver\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return Cursor
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->locked) {
            throw new LogicException(
                sprintf(
                    'Method %s cannot be called after iteration over %s has began',
                    __METHOD__,
                    __CLASS__
                )
            );
        }

        if (null === $this->arrayRepresentation) {
            $this->arrayRepresentation = $this->mongoCursor->toArray();
        }

        return $this->arrayRepresentation;
    }

    /**
     * @return \Generator
     */
    protected function createGenerator()
    {
        foreach ($this->mongoCursor as $document) {
            yield $document;
        }
    }

    protected function initMongoCursor()
    {
        $this->mongoCursor = $this->server->executeCommand(
            $this->databaseName,
            new Command($this->options)
        );

        $this->mongoCursor->setTypeMap($this->typeMap);
    }
}