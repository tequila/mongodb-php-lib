<?php

namespace Tequilla\MongoDB;

use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Command\CommandBuilder;
use Tequilla\MongoDB\Command\Type\CreateCollectionType;
use Tequilla\MongoDB\Command\Type\DropCollectionType;
use Tequilla\MongoDB\Command\Type\DropDatabaseType;
use Tequilla\MongoDB\Command\Type\ListCollectionsType;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\Driver\TypeMapOptions;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

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
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Database name must be a string, "%s" is given',
                    get_type($name)
                )
            );
        }

        $this->manager = $manager;

        $options = $this->resolveOptions($options);

        $this->name = $name;
        $this->readConcern = $options['readConcern'];
        $this->readPreference = $options['readPreference'];
        $this->writeConcern = $options['writeConcern'];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ReadConcern
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
     * @return WriteConcern
     */
    public function getWriteConcern()
    {
        return $this->writeConcern;
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        if (!is_string($collectionName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Collection name must be a string, %s given',
                    get_type($collectionName)
                )
            );
        }

        $options['create'] = $collectionName;

        return $this->executeCommand(CreateCollectionType::class, $options);
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function drop(array $options = [])
    {
        $cursor = $this
            ->createCommandBuilder()
            ->buildCommand(DropDatabaseType::class)
            ->execute($options);

        return TypeMapOptions::setArrayTypeMapOnCursor($cursor)->toArray();
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        $options['drop'] = (string) $collectionName;

        return $this->executeCommand(DropCollectionType::class, $options);
    }

    /**
     * @param array $options
     * @return array
     */
    public function listCollections(array $options = [])
    {
        return $this->executeCommand(ListCollectionsType::class, $options);
    }

    public function selectCollection($collectionName, array $options = [])
    {

    }

    /**
     * @param array $options
     * @return
     */
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

        try {
            return $resolver->resolve($options);
        } catch(OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param $commandTypeClass
     * @param array $options
     * @return array
     */
    private function executeCommand($commandTypeClass, array $options)
    {
        $cursor =  $this
            ->createCommandBuilder()
            ->buildCommand((string) $commandTypeClass)
            ->execute($options);

        return TypeMapOptions::setArrayTypeMapOnCursor($cursor)->toArray();
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
