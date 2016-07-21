<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Connection;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use function Tequilla\MongoDB\ensureIsSubclassOf;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

/**
 * Class Command
 * @package Tequilla\MongoDB\Command
 */
class CommandWrapper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @var string
     */
    private $commandClass;

    /**
     * Command constructor.
     * @param Connection $connection
     * @param string $databaseName
     * @param string $commandClass
     */
    public function __construct(
        Connection $connection,
        $databaseName,
        $commandClass
    ) {
        ensureIsSubclassOf($commandClass, CommandTypeInterface::class);

        $this->databaseName = (string) $databaseName;
        $this->connection = $connection;
        $this->commandClass = (string) $commandClass;
        $this->readPreference = $commandClass::getDefaultReadPreference();
    }

    /**
     * @param array $options
     * @return array
     */
    public function execute(array $options = [])
    {
        $resolver = new OptionsResolver();
        $commandClass = $this->commandClass;
        $commandClass::configureOptions($resolver);
        $commandName = $commandClass::getCommandName();
        $resolver->setRequired($commandName);
        
        try {
            $options = $resolver->resolve($options);
        } catch(OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $commandValue = $options[$commandName];
        $command = [$commandName => $commandValue] + $options;

        return $this->connection->executeCommand(
            $this->databaseName,
            $command,
            $this->readPreference
        );
    }

    /**
     * @param ReadPreference $readPreference
     * @return $this
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $commandClass = $this->commandClass;
        if (!$commandClass::supportsReadPreference($readPreference)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Command "%s" does not support "%s" read preference',
                    $commandClass,
                    $readPreference->getMode()
                )
            );
        }

        $this->readPreference = $readPreference;

        return $this;
    }
}
