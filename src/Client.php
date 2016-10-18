<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\CommandInterface;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListDatabases;
use Tequila\MongoDB\Command\Result\DatabaseInfo;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\Connection\ConnectionOptions;

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
    public function __construct($uri = 'mongodb://127.0.0.1/', array $uriOptions = [], array $driverOptions = [])
    {
        $uriOptions = ConnectionOptions::resolve($uriOptions);

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

        return current(iterator_to_array($cursor));
    }

    /**
     * @return DatabaseInfo[]
     */
    public function listDatabases()
    {
        $cursor = (new ListDatabases())->execute($this->manager);
        $result = current(iterator_to_array($cursor));

        if (isset($result['databases']) && is_array($result['databases'])) {
            return array_map(function(array $dbInfo) {
                return new DatabaseInfo($dbInfo);
            }, $result['databases']);
        }

        throw new UnexpectedResultException(
            'Command "listDatabases" did not return expected "databases" array'
        );
    }

    /**
     * @param CommandInterface $command
     * @return \MongoDB\Driver\Cursor
     */
    public function runCommand(CommandInterface $command)
    {
        return $command->execute($this->manager);
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