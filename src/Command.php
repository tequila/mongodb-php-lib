<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolver;

class Command implements CommandInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CompatibilityResolver
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
     * {@inheritdoc}
     */
    public function getOptions(Server $server)
    {
        if (null !== $this->compatibilityResolver) {
            $this->compatibilityResolver->resolveCompatibilities($server, $this->options);
        }

        return $this->options;
    }

    /**
     * @param CompatibilityResolver $resolver
     */
    public function setCompatibilityResolver(CompatibilityResolver $resolver)
    {
        $this->compatibilityResolver = $resolver;
    }
}
