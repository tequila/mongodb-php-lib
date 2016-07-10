<?php

namespace Tequilla\MongoDB\Command;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;

/**
 * Class Command
 * @package Tequilla\MongoDB\Command
 */
class CommandWrapper
{
    /**
     * @var Manager
     */
    private $manager;

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
     * @param Manager $manager
     * @param string $databaseName
     * @param string $commandClass
     */
    public function __construct(
        Manager $manager,
        $databaseName,
        $commandClass
    ) {
        $this->ensureValidCommandClass($commandClass);

        $this->databaseName = (string) $databaseName;
        $this->manager = $manager;
        $this->commandClass = (string) $commandClass;
        $this->readPreference = $commandClass::getDefaultReadPreference();
    }

    /**
     * @param array $options
     * @return \MongoDB\Driver\Cursor
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
        $options = [$commandName => $commandValue] + $options;

        $command = new Command($options);

        return $this->manager->executeCommand(
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
            throw new \InvalidArgumentException(
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

    /**
     * @param string $commandClass
     */
    private function ensureValidCommandClass($commandClass)
    {
        if (!class_exists($commandClass)) {
            throw new \InvalidArgumentException(
                sprintf('Class "%s" is not found', $commandClass)
            );
        }

        if (!is_subclass_of($commandClass, CommandTypeInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$commandClass must be a class, implementing "%s"',
                    CommandTypeInterface::class
                )
            );
        }
    }
}
