<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;

class Command implements CommandInterface, CompiledCommandInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CompatibilityResolverInterface
     */
    private $resolver;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        if (null !== $this->resolver) {
            $options = new CommandOptions($this->options);
            $options->setServer($server);
            $this->resolver->resolveCompatibilities($options);

            $this->options = $options->toArray();
        }

        return $this->options;
    }

    /**
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @return CursorInterface
     */
    public function execute(ManagerInterface $manager, $databaseName)
    {
        $readPreference = $this->readPreference ?: new ReadPreference(ReadPreference::RP_PRIMARY);
        $cursor = $manager->executeCommand($databaseName, $this, $readPreference);

        return $cursor;
    }

    /**
     * @param ReadPreference $readPreference
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;
    }

    public function setCompatibilityResolver(CompatibilityResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
}