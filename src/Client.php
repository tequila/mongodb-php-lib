<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListDatabases;
use Tequila\MongoDB\Command\Result\DatabaseInfo;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\Connection\ConnectionOptions;
use Tequila\MongoDB\Options\Driver\DriverOptions;
use Tequila\MongoDB\Options\Driver\TypeMapOptions;

class Client
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $typeMap;

    /**
     * @param string $uri
     * @param array $uriOptions
     * @param array $driverOptions
     */
    public function __construct($uri = 'mongodb://localhost:27017', array $uriOptions = [], array $driverOptions = [])
    {
        $uriOptions = ConnectionOptions::resolve($uriOptions);
        $driverOptions = DriverOptions::resolve($driverOptions);

        $this->typeMap = $driverOptions['typeMap'];
        unset($driverOptions['typeMap']);

        $this->uri = $uri;
        $this->manager = new Manager((string)$uri, $uriOptions, $driverOptions);
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $command = new DropDatabase($databaseName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getArrayTypeMap());

        return current($cursor->toArray());
    }

    /**
     * @return DatabaseInfo[]
     */
    public function listDatabases()
    {
        $cursor = (new ListDatabases())->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getArrayTypeMap());
        $result = current($cursor->toArray());

        if (!isset($result['databases']) && is_array($result['databases'])) {
            return array_map(function(array $dbInfo) {
                return new DatabaseInfo($dbInfo);
            }, $result['databases']);
        }

        throw new UnexpectedResultException(
            'Command listDatabases did not return expected "databases" array'
        );
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     * @return Collection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        $options += ['typeMap' => $this->typeMap];

        return new Collection($this->manager, $databaseName, $collectionName, $options);
    }

    /**
     * @param string $databaseName
     * @param $options
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        $options += ['typeMap' => $this->typeMap];

        return new Database($this->manager, $databaseName, $options);
    }
}