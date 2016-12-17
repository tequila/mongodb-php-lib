<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;

class Command implements CommandInterface
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
     * @return ReadPreference
     */
    public function getReadPreference()
    {
        return $this->readPreference;
    }

    /**
     * @param ReadPreference $readPreference
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;
    }

    /**
     * @param CompatibilityResolverInterface $resolver
     */
    public function setCompatibilityResolver(CompatibilityResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
}