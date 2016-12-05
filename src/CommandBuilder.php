<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\OptionsResolver\Command\ReadConcernAwareInterface;
use Tequila\MongoDB\OptionsResolver\Command\ReadPreferenceResolverInterface;
use Tequila\MongoDB\OptionsResolver\Command\WriteConcernAwareInterface;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\OptionsResolver\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;

class CommandBuilder implements CommandBuilderInterface
{
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
     * @inheritdoc
     */
    public function createCommand(array $command, array $options, $resolverClass)
    {
        $resolver = $this->getResolver($resolverClass);
        $options = $resolver->resolve($options);

        if ($resolver instanceof ReadPreferenceResolverInterface) {
            $readPreference = $resolver->resolveReadPreference($options, $this->readPreference);
            unset($options['readPreference']);
        } else {
            $readPreference = null;
        }

        $commandOptions = $command + $options;
        $command = new Command($commandOptions, $resolver);

        if (null !== $readPreference) {
            $command->setReadPreference($readPreference);
        }

        if ($resolver instanceof CompatibilityResolverInterface) {
            $command->setCompatibilityResolver($resolver);
        }

        return $command;
    }

    /**
     * @inheritdoc
     */
    public function setReadConcern(ReadConcern $readConcern)
    {
        $this->readConcern = $readConcern;
    }

    /**
     * @inheritdoc
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;
    }

    /**
     * @inheritdoc
     */
    public function setWriteConcern(WriteConcern $writeConcern)
    {
        $this->writeConcern = $writeConcern;
    }

    /**
     * @param $resolverClass
     * @return OptionsResolver
     */
    private function getResolver($resolverClass)
    {
        $resolver = clone ResolverFactory::get($resolverClass);
        if ($resolver instanceof ReadConcernAwareInterface && $this->readConcern) {
            $resolver->setDefaultReadConcern($this->readConcern);
            ReadConcernConfigurator::configure($resolver);
        }

        if ($resolver instanceof WriteConcernAwareInterface && $this->writeConcern) {
            $resolver->setDefaultWriteConcern($this->writeConcern);
            WriteConcernConfigurator::configure($resolver);
        }

        if ($resolver instanceof ReadPreferenceResolverInterface) {
            ReadPreferenceConfigurator::configure($resolver);
        }

        return $resolver;
    }
}