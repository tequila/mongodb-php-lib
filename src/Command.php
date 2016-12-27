<?php

namespace Tequila\MongoDB;

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
    private $compatibilityResolver;

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
        if (null !== $this->compatibilityResolver) {
            $options = new CommandOptions($this->options);
            $options->setServer($server);
            $this->compatibilityResolver->resolveCompatibilities($options);

            $this->options = $options->toArray();
        }

        return $this->options;
    }

    /**
     * @param CompatibilityResolverInterface $resolver
     */
    public function setCompatibilityResolver(CompatibilityResolverInterface $resolver)
    {
        $this->compatibilityResolver = $resolver;
    }
}