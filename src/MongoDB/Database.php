<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Command\Type\CreateCollectionType;
use Tequilla\MongoDB\Command\Type\DropDatabaseType;
use Tequilla\MongoDB\Command\Type\ListCollectionsType;
use Tequilla\MongoDB\Options\Driver\TypeMapOptions;

class Database implements DatabaseInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var CommandBuilder
     */
    private $commandBuilder;

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
     * Database constructor.
     * @param Manager $manager
     * @param string $name
     * @param array $options
     */
    public function __construct(Manager $manager, $name, array $options = [])
    {
        $this->manager = $manager;

        $options = $this->resolveOptions($options);
        
        $this->name = $name;
        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
    }
    
    public function getReadConcern()
    {
        return $this->readConcern;
    }
    
    public function getReadPreference()
    {
        return $this->readPreference;
    }
    
    public function getWriteConcern()
    {
        return $this->writeConcern;
    }

    public function createCollection($name, array $options = [])
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Name of the collection must be a string, %s given',
                    is_object($name) ? get_class($name) : gettype($name)
                )
            );
        }

        $options['create'] = $name;

        return $this
            ->createCommandBuilder()
            ->buildCommand(CreateCollectionType::class)
            ->execute($options);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function drop(array $options = [])
    {
        return $this
            ->createCommandBuilder()
            ->buildCommand(DropDatabaseType::class)
            ->execute($options);
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function listCollections(array $options = [])
    {
        return $this
            ->createCommandBuilder()
            ->buildCommand(ListCollectionsType::class)
            ->execute($options);
    }

    public function selectCollection($collectionName, array $options = [])
    {

    }

    private function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'readConcern' => $this->manager->getReadConcern(),
            'readPreference' => $this->manager->getReadPreference(),
            'writeConcern' => $this->manager->getWriteConcern(),
        ]);

        $resolver->setAllowedTypes('readConcern', ReadConcern::class);
        $resolver->setAllowedTypes('readPreference', ReadPreference::class);
        $resolver->setAllowedTypes('writeConcern', WriteConcern::class);

        TypeMapOptions::configureOptions($resolver);
        
        return $resolver->resolve($options);
    }

    /**
     * @return CommandBuilder
     */
    private function createCommandBuilder()
    {
        if (!$this->commandBuilder) {
            $this->commandBuilder = new CommandBuilder($this->manager, $this->name);
        }

        return $this->commandBuilder;
    }
}