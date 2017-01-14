<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Exception\UnexpectedResultException;
use Tequila\MongoDB\OptionsResolver\TypeMapResolver;
use Tequila\MongoDB\Traits\CommandExecutorTrait;
use Tequila\MongoDB\Traits\ResolveReadWriteOptionsTrait;

class Client
{
    use CommandExecutorTrait;
    use ResolveReadWriteOptionsTrait;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param Manager $manager
     * @param array $options
     */
    public function __construct(Manager $manager, array $options = [])
    {
        $this->manager = $manager;
        $this->resolveReadWriteOptions($options);
    }

    /**
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        $cursor = $this
            ->getCommandExecutor()
            ->executeCommand(
                $this->manager,
                $databaseName,
                ['dropDatabase' => 1],
                $options
            );

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
        $cursor = $this->manager->executeCommand(
            'admin',
            new SimpleCommand(['listDatabases' => 1]),
            new ReadPreference(ReadPreference::RP_PRIMARY)
        );
        $cursor->setTypeMap(TypeMapResolver::resolveStatic([]));
        $result = $cursor->current();

        if (!isset($result['databases']) || !is_array($result['databases'])) {
            throw new UnexpectedResultException(
                'Command "listDatabases" did not return expected "databases" array.'
            );
        }

        return $result['databases'];
    }

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array $options
     * @return Collection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'writeConcern' => $this->writeConcern,
        ];

        return new Collection($this->manager, $databaseName, $collectionName, $options);
    }

    /**
     * @param string $databaseName
     * @param $options
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'writeConcern' => $this->writeConcern,
        ];

        return new Database($this->manager, $databaseName, $options);
    }
}