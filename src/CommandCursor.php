<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Server;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class CommandCursor
{
    /**
     * @var Cursor
     */
    private $mongoCursor;

    /**
     * @var array
     */
    private $arrayRepresentation;

    /**
     * @param Server $server
     * @param $databaseName
     * @param array $options
     */
    public function __construct(Server $server, $databaseName, array $options)
    {
        if (isset($options['typeMap'])) {
            $typeMap = $options['typeMap'];
            unset($options['typeMap']);
        } else {
            $typeMap = TypeMapOptions::getDefaultTypeMap();
        }

        $this->mongoCursor = $server->executeCommand($databaseName, new Command($options));
        $this->mongoCursor->setTypeMap($typeMap);
    }

    /**
     * @return \MongoDB\Driver\Server
     */
    public function getServer()
    {
        return $this->mongoCursor->getServer();
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
        if (null === $this->arrayRepresentation) {
            $this->arrayRepresentation = $this->mongoCursor->toArray();
        }

        return $this->arrayRepresentation;
    }
}