<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Command\ReadConcernAwareInterface;
use Tequila\MongoDB\Command\ReadPreferenceResolverInterface;
use Tequila\MongoDB\Command\WriteConcernAwareInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\Configurator\ReadConcernConfigurator;
use Tequila\MongoDB\Options\Configurator\ReadPreferenceConfigurator;
use Tequila\MongoDB\Options\Configurator\WriteConcernConfigurator;
use Tequila\MongoDB\Options\OptionsResolver;

class CommandBuilder implements CommandBuilderInterface
{
    /**
     * @var OptionsResolver[]
     */
    private static $cache = [];

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
        if (!is_string($resolverClass)) {
            throw new InvalidArgumentException('$resolverClass must be a string');
        }

        if (!array_key_exists($resolverClass, self::$cache)) {
            if (!class_exists($resolverClass)) {
                throw new InvalidArgumentException(
                    sprintf('Resolver class "%s" is not found', $resolverClass)
                );
            }

            if (!is_subclass_of($resolverClass, OptionsResolver::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Resolver class "%s" must extend "%s"',
                        $resolverClass,
                        OptionsResolver::class
                    )
                );
            }

            /** @var OptionsResolver $resolver */
            $resolver = new $resolverClass;
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

            $resolver->configureOptions();

            self::$cache[$resolverClass] = new $resolverClass;
        }

        return self::$cache[$resolverClass];
    }
}