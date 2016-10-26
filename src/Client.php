<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListDatabases;
use Tequila\MongoDB\Command\Result\DatabaseInfo;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Options\TypeMapOptions;

class Client
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $command = new DropDatabase($databaseName, $options);
        $cursor = $this->executeCommand($databaseName, $command);

        return current(iterator_to_array($cursor));
    }

    /**
     * @return DatabaseInfo[]
     */
    public function listDatabases()
    {
        $cursor = $this->executeCommand('admin', new ListDatabases());
        $result = current(iterator_to_array($cursor));

        if (isset($result['databases']) && is_array($result['databases'])) {
            return array_map(function (array $dbInfo) {
                return new DatabaseInfo($dbInfo);
            }, $result['databases']);
        }

        throw new UnexpectedResultException(
            'Command "listDatabases" did not return expected "databases" array'
        );
    }

    /**
     * @param string $databaseName
     * @param CommandInterface $command
     * @param ReadPreference $readPreference
     * @param array $typeMap
     * @return \MongoDB\Driver\Cursor
     */
    public function executeCommand(
        $databaseName,
        CommandInterface $command,
        ReadPreference $readPreference = null,
        array $typeMap = []
    ) {
        $cursor = $this->manager->executeCommand($databaseName, $command, $readPreference);
        $typeMap = TypeMapOptions::resolve($typeMap);
        $cursor->setTypeMap($typeMap);

        return $cursor;
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