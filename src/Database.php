<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\OptionsResolver\Command\CreateCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropDatabaseResolver;
use Tequila\MongoDB\OptionsResolver\Command\ListCollectionsResolver;
use Tequila\MongoDB\OptionsResolver\DatabaseOptionsResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;
use Tequila\MongoDB\Traits\CommandBuilderTrait;
use Tequila\MongoDB\Traits\ExecuteCommandTrait;

class Database
{
    use CommandBuilderTrait;
    use ExecuteCommandTrait;

    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

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
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @param array $options
     */
    public function __construct(ManagerInterface $manager, $databaseName, array $options = [])
    {
        $this->manager = $manager;
        $this->databaseName = $databaseName;

        $options += [
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ];

        $options = ResolverFactory::get(DatabaseOptionsResolver::class)->resolve($options);

        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        $cursor = $this->executeCommand(
            ['create' => $collectionName],
            $options,
            CreateCollectionResolver::class
        );

        return $cursor->current();
    }

    /**
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        $cursor = $this->executeCommand(
            ['dropDatabase' => 1],
            $options,
            DropDatabaseResolver::class
        );

        return $cursor->current();
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        $cursor = $this->executeCommand(
            ['drop' => $collectionName],
            $options,
            DropCollectionResolver::class
        );

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
        return $this->executeCommand(
            ['listCollections' => 1],
            $options,
            ListCollectionsResolver::class
        );
    }

    /**
     * @param CommandInterface $command
     * @param ReadPreference|null $readPreference
     * @return CursorInterface
     */
    public function runCommand(CommandInterface $command, ReadPreference $readPreference = null)
    {
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
