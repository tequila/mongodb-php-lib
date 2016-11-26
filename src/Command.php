<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\ServerCompatibleOptions;

class Command implements CommandInterface, CompiledCommandInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @param array $options
     * @param CompatibilityResolverInterface $resolver
     */
    public function __construct(array $options, CompatibilityResolverInterface $resolver)
    {
        $this->options = $options;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        $options = new ServerCompatibleOptions($this->options);
        $options->setServer($server);
        $this->resolver->resolveCompatibilities($options);

        return $options->toArray();
    }

    /**
     * @param ManagerInterface $manager
     * @param string $databaseName
     * @return CursorInterface
     */
    public function execute(ManagerInterface $manager, $databaseName)
    {
        $readPreference = $this->readPreference ?: new ReadPreference(ReadPreference::RP_PRIMARY);

        return $manager->executeCommand($databaseName, $this, $readPreference);
    }

    /**
     * @param ReadPreference $readPreference
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;
    }
}