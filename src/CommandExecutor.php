<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\OptionsResolver\Command\AggregateResolver;
use Tequila\MongoDB\OptionsResolver\Command\CountResolver;
use Tequila\MongoDB\OptionsResolver\Command\CreateCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\CreateIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\DistinctResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropCollectionResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropDatabaseResolver;
use Tequila\MongoDB\OptionsResolver\Command\DropIndexesResolver;
use Tequila\MongoDB\OptionsResolver\Command\FindAndModifyResolver;
use Tequila\MongoDB\OptionsResolver\Command\ListCollectionsResolver;
use Tequila\MongoDB\OptionsResolver\Command\ReadConcernAwareInterface;
use Tequila\MongoDB\OptionsResolver\Command\WriteConcernAwareInterface;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\OptionsResolver\TypeMapResolver;

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
     * @var array
     */
    private $cache = [];

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
     * @inheritdoc
     */
    public function executeCommand(ManagerInterface $manager, $databaseName, array $command, array $options)
    {
        if (empty($command)) {
            throw new InvalidArgumentException('$command cannot be empty.');
        }

        $resolver = $this->getResolver($command);
        $options = $resolver->resolve($options);

        if ($resolver->isDefined('readPreference') && isset($options['readPreference'])) {
            $readPreference = $options['readPreference'];
            unset($options['readPreference']);
        } else {
            $readPreference = $this->readPreference;
        }

        $commandOptions = $command + $options;
        $command = new Command($commandOptions);

        if ($resolver instanceof CompatibilityResolverInterface) {
            $command->setCompatibilityResolver($resolver);
        }

        /** @var CursorInterface $cursor */
        $cursor = $manager->executeCommand($databaseName, $command, $readPreference);
        $cursor->setTypeMap(TypeMapResolver::getDefault());

        return $cursor;
    }

    /**
     * @param array $command
     * @return OptionsResolver
     */
    private function getResolver(array $command)
    {
        $commandName = key($command);

        if (!isset($this->cache[$commandName])) {
            if (!isset(self::$resolverClassesByCommandName[$commandName])) {
                throw new LogicException(
                    sprintf('OptionsResolver for command "%s" does not exist.', $commandName)
                );
            }
            $resolverClass = self::$resolverClassesByCommandName[$commandName];
            $resolver = clone OptionsResolver::get($resolverClass);

            if ($resolver instanceof ReadConcernAwareInterface) {
                $resolver->setDefaultReadConcern($this->readConcern);
            }

            if ($resolver instanceof WriteConcernAwareInterface) {
                $resolver->setDefaultWriteConcern($this->writeConcern);
            }

            $this->cache[$commandName] = $resolver;
        }

        return $this->cache[$commandName];
    }
}