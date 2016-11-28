<?php

namespace Tequila\MongoDB\Traits;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\CommandBuilder;
use Tequila\MongoDB\CommandBuilderInterface;
use Tequila\MongoDB\Exception\LogicException;

trait CommandBuilderTrait
{
    /**
     * @var CommandBuilderInterface
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
     * @param CommandBuilderInterface $commandBuilder
     */
    public function setCommandBuilder(CommandBuilderInterface $commandBuilder)
    {
        if (null !== $this->commandBuilder) {
            throw new LogicException(
                sprintf(
                    'Command builder cannot be changed on the "%s" instance once it has already been set.',
                    get_class($this)
                )
            );
        }

        $commandBuilder->setReadConcern($this->readConcern);
        $commandBuilder->setReadPreference($this->readPreference);
        $commandBuilder->setWriteConcern($this->writeConcern);

        $this->commandBuilder = $commandBuilder;
    }

    /**
     * @return CommandBuilderInterface
     */
    public function getCommandBuilder()
    {
        if (null === $this->commandBuilder) {
            $this->commandBuilder = new CommandBuilder();
            $this->commandBuilder->setReadConcern($this->readConcern);
            $this->commandBuilder->setReadPreference($this->readPreference);
            $this->commandBuilder->setWriteConcern($this->writeConcern);
        }

        return $this->commandBuilder;
    }
}