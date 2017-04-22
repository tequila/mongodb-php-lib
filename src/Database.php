<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Exception\RuntimeException as MongoDBRuntimeException;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Traits\CommandExecutorTrait;
use Tequila\MongoDB\Traits\ExecuteCommandTrait;

class Database
{
    use CommandExecutorTrait;
    use ExecuteCommandTrait;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var WriteConcern
     */
    private $writeConcern;

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(Manager $manager, $databaseName, array $options = [])
    {
        if (!$databaseName) {
            throw new InvalidArgumentException('$databaseName cannot be empty.');
        }

        $options += [
            'readConcern' => $manager->getReadConcern(),
            'readPreference' => $manager->getReadPreference(),
            'writeConcern' => $manager->getWriteConcern(),
        ];

        $validTypes = [
            'readConcern' => ReadConcern::class,
            'readPreference' => ReadPreference::class,
            'writeConcern' => WriteConcern::class,
        ];

        foreach ($validTypes as $optionName => $validType) {
            if (!$options[$optionName] instanceof $validType) {
                throw new InvalidArgumentException(
                    'Option "%s" is expected to be an instance of %s, %s given.',
                    $optionName,
                    $validType,
                    getType($options[$optionName])
                );
            }
        }

        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];

        $this->manager = $manager;
        $this->databaseName = $databaseName;
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
     * @return Cursor
     */
    public function listCollections(array $options = [])
    {
        return $this->executeCommand(['listCollections' => 1], $options);
    }

    /**
     * @param array|object $command
     * @param array $options
     * @return CommandCursor
     */
    public function runCommand($command, array $options = [])
    {
        if (!is_array($command) && !is_object($command)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$command must be an array or an object, %s given.',
                    \Tequila\MongoDB\getType($command)
                )
            );
        }

        $readPreference = isset($options['readPreference'])
            ? $options['readPreference']
            : $this->readPreference;

        if (!$readPreference instanceof ReadPreference) {
            throw new InvalidArgumentException(
                sprintf(
                    'Option "readPreference" is expected to be an instance of %s, %s given.',
                    ReadPreference::class,
                    \Tequila\MongoDB\getType($readPreference)
                )
            );
        }

        if (!$command instanceof $command) {
            $command = new \MongoDB\Driver\Command($command);
        }
        $server = $this->manager->selectServer($readPreference);
        $cursor = $server->executeCommand($this->databaseName, $command);

        return new CommandCursor($cursor);
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
