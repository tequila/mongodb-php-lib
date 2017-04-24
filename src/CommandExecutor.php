<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\OptionsResolver\Command\AggregateResolver;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolver;
use Tequila\MongoDB\OptionsResolver\Command\CountResolver;
use Tequila\MongoDB\OptionsResolver\Command\CreateCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\CreateIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\DistinctResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropDatabaseResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindAndModifyResolver;
use Tequila\MongoDB\OptionsResolver\Command\ListCollectionsResolver;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class CommandExecutor
{
    /**
     * @var array
     */
    private static $resolverClassesByCommandName = [
        'aggregate' => AggregateResolver::class,
        'count' => CountResolver::class,
        'create' => CreateCollectionResolver::class,
        'createIndexes' => CreateIndexesResolver::class,
        'distinct' => DistinctResolver::class,
        'drop' => DropCollectionResolver::class,
        'dropDatabase' => DropDatabaseResolver::class,
        'dropIndexes' => DropIndexesResolver::class,
        'findAndModify' => FindAndModifyResolver::class,
        'listCollections' => ListCollectionsResolver::class,
    ];

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
     * @param ReadConcern $readConcern
     * @param ReadPreference $readPreference
     * @param WriteConcern $writeConcern
     */
    public function __construct(ReadConcern $readConcern, ReadPreference $readPreference, WriteConcern $writeConcern)
    {
        $this->readConcern = $readConcern;
        $this->readPreference = $readPreference;
        $this->writeConcern = $writeConcern;
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand(Manager $manager, $databaseName, array $command, array $options)
    {
        if (empty($command)) {
            throw new InvalidArgumentException('$command cannot be empty.');
        }

        $resolver = $this->getResolver($command);

        // Command should inherit readConcern, readPreference and writeConcern from Client, Database or Collection
        // instance, from which it is called, due to MongoDB Driver Specifications
        foreach (['readConcern', 'readPreference', 'writeConcern'] as $optionToBeInherited) {
            if ($resolver->isDefined($optionToBeInherited) && !$resolver->hasDefault($optionToBeInherited)) {
                $resolver->setDefault($optionToBeInherited, $this->$optionToBeInherited);
            }
        }

        $options = $resolver->resolve($options);
        if (isset($options['typeMap'])) {
            $typeMap = $options['typeMap'];
            unset($options['typeMap']);
        } else {
            $typeMap = ['root' => 'array', 'document' => 'array', 'array' => 'array'];
        }

        if ($resolver->isDefined('readPreference') && isset($options['readPreference'])) {
            $readPreference = $options['readPreference'];
            unset($options['readPreference']);
        } else {
            $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        $command = new Command($command + $options);
        $command->setCompatibilityResolver(new CompatibilityResolver($resolver));

        /** @var Cursor $cursor */
        $cursor = $manager->executeCommand($databaseName, $command, $readPreference);
        $cursor->setTypeMap($typeMap);

        // Clean OptionsResolver from default readConcern, readPreference and writeConcern.
        // This allows to reuse the same resolver in other commands, that may be called from different
        // Client, Database or Collection instances with different default readConcern, readPreference and writeConcern
        foreach (['readConcern', 'readPreference', 'writeConcern'] as $inheritedOption) {
            if (
                $resolver->isDefined($inheritedOption)
                && $resolver->hasDefault($inheritedOption)
                && $this->$inheritedOption === $resolver->getDefault($inheritedOption)
            ) {
                $resolver->removeDefault($inheritedOption);
            }
        }

        return $cursor;
    }

    /**
     * @param array $command
     * @return OptionsResolver
     */
    private function getResolver(array $command)
    {
        $commandName = key($command);

        if (!isset(self::$resolverClassesByCommandName[$commandName])) {
            throw new LogicException(
                sprintf('OptionsResolver for command "%s" does not exist.', $commandName)
            );
        }
        $resolverClass = self::$resolverClassesByCommandName[$commandName];

        return OptionsResolver::get($resolverClass);
    }
}
