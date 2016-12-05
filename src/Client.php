<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\Command\DropDatabaseResolver;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\OptionsResolver\TypeMapResolver;
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
        $cursor = $this->commandBuilder
            ->createCommand(
                ['dropDatabase' => 1],
                $options,
                DropDatabaseResolver::class
            )
            ->execute($this->manager, $databaseName);

        $cursor->setTypeMap(TypeMapResolver::getDefault());

        return $cursor->current();
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
     * @return array
     */
    public function listDatabases()
    {
        $cursor = $this->runCommand(
            'admin',
            new SimpleCommand(['listDatabases' => 1]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );
        $cursor->setTypeMap(TypeMapResolver::getDefault());
        $result = $cursor->current();

        if (isset($result['databases']) && is_array($result['databases'])) {
            throw new UnexpectedResultException(
                'Command "listDatabases" did not return expected "databases" array'
            );
        }

        return $result;
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