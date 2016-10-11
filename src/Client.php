<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\BSON\BSONDocument;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListDatabases;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\Connection\ConnectionOptions;
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
     * @param string $uri
     * @param array $uriOptions
     * @param array $driverOptions
     */
    public function __construct($uri = 'mongodb://localhost:27017', array $uriOptions = [], array $driverOptions = [])
    {
        $uriOptions = ConnectionOptions::resolve($uriOptions);

        $this->uri = $uri;
        $this->manager = new Manager((string)$uri, $uriOptions, $driverOptions);
    }

    /**
     * @param $databaseName
     * @param array $options
     * @return BSONDocument
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $command = new DropDatabase($databaseName, $options);
        $cursor = $command->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaultTypeMap());

        return current($cursor->toArray());
    }

    /**
     * @return array
     */
    public function listDatabases()
    {
        $cursor = (new ListDatabases())->execute($this->manager);
        $cursor->setTypeMap(TypeMapOptions::getDefaultTypeMap());
        $result = current($cursor->toArray());

        if (isset($result['databases']) && is_array($result['databases'])) {
            return $result['databases'];
        }

        throw new UnexpectedResultException(
            'listDatabases command did not return expected "databases" array'
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
        return new Collection($this->manager, $databaseName, $collectionName, $options);
    }

    /**
     * @param string $databaseName
     * @param $options
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        return new Database($this->manager, $databaseName, $options);
    }
}