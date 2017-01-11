<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Traits\CommandExecutorTrait;
use Tequila\MongoDB\Traits\ExecuteCommandTrait;
use Tequila\MongoDB\Traits\ResolveReadWriteOptionsTrait;

class Database
{
    use CommandExecutorTrait;
    use ExecuteCommandTrait;
    use ResolveReadWriteOptionsTrait;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(Manager $manager, $databaseName, array $options = [])
    {
        $this->manager = $manager;
        $this->databaseName = $databaseName;
        $this->resolveReadWriteOptions($options);
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        $cursor = $this->executeCommand(['create' => $collectionName], $options);

        return $cursor->current();
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $cursor = $this->executeCommand(['dropDatabase' => 1], $options);

        return $cursor->current();
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        try {
            $cursor = $this->executeCommand(['drop' => $collectionName], $options);
        } catch(MongoDBRuntimeException $e) {
            if('ns not found' === $e->getMessage()) {
                return ['ok' => 0, 'errmsg' => $e->getMessage()];
            }

            throw $e;
        }

        return $cursor->current();
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param array $options
     * @return CursorInterface
     */
    public function listCollections(array $options = [])
    {
        return $this->executeCommand(['listCollections' => 1], $options);
    }

    /**
     * @param CommandInterface $command
     * @param ReadPreference|null $readPreference
     * @return CursorInterface
     */
    public function runCommand(CommandInterface $command, ReadPreference $readPreference = null)
    {
        $readPreference = $readPreference ?: $this->readPreference;

        return $this->manager->executeCommand($this->databaseName, $command, $readPreference);
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return Collection
     */
    public function selectCollection($collectionName, array $options = [])
    {
        $options += [
            'readConcern' => $this->readConcern,
            'readPreference' => $this->readPreference,
            'writeConcern' => $this->writeConcern,
        ];

        return new Collection($this->manager, $this->databaseName, $collectionName, $options);
    }
}
