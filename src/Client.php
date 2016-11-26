<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Command\DropDatabase;
use Tequila\MongoDB\Command\ListDatabases;
use Tequila\MongoDB\Command\Result\DatabaseInfo;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\Traits\CommandBuilderTrait;

class Client
{
    use CommandBuilderTrait;

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
        $this->readConcern = $manager->getReadConcern();
        $this->readPreference = $manager->getReadPreference();
        $this->writeConcern = $manager->getWriteConcern();
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $command = new DropDatabase($options);
        $cursor = $this->runCommand($databaseName, $command);

        return current(iterator_to_array($cursor));
    }

    /**
     * @return \MongoDB\Driver\ReadConcern
     */
    public function getReadConcern()
    {
        return $this->readConcern;
    }

    /**
     * @return ReadPreference
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    /**
     * @return \MongoDB\Driver\WriteConcern
     */
    public function getWriteConcern()
    {
        return $this->writeConcern;
    }

    /**
     * @return DatabaseInfo[]
     */
    public function listDatabases()
    {
        $cursor = $this->runCommand('admin', new ListDatabases());
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
     * @return CursorInterface
     */
    public function runCommand(
        $databaseName,
        CommandInterface $command,
        ReadPreference $readPreference = null
    ) {
        return $this->manager->executeCommand($databaseName, $command, $readPreference);
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