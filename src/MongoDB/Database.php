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
     * @return ReadConcern
     */
    public function getReadConcern()
    {
        return $this->readConcern;
    }

    /**
     * @return [type] [description]
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    public function getWriteConcern()
    {
        return $this->writeConcern;
    }

    /**
     * @param string $name
     * @param array $options
     * @return array
     */
    public function createCollection($name, array $options = [])
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Collection name must be a string, %s given',
                    get_type($name)
                )
            );
        }

        $options['create'] = $name;

        $cursor = $this
            ->createCommandBuilder()
            ->buildCommand(CreateCollectionType::class)
            ->execute($options);

        return $this->setTypeMapOnCursor($cursor)->toArray();
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
        $cursor = $this
            ->createCommandBuilder('admin')
            ->buildCommand(DropDatabaseType::class)
            ->execute($options);

        return $this->setTypeMapOnCursor($cursor)->toArray();
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function listCollections(array $options = [])
    {
        $cursor =  $this
            ->createCommandBuilder()
            ->buildCommand(ListCollectionsType::class)
            ->execute($options);

        return $this->setTypeMapOnCursor($cursor)->toArray();
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

        try {
            return $resolver->resolve($options);
        } catch(OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
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

    private function setTypeMapOnCursor(Cursor $cursor)
    {
        $cursor->setTypeMap([
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ]);

        return $cursor;
    }
}
