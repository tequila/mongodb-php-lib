<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Options\OptionsResolver;

class ResolverAwareCommand implements CommandInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var bool
     */
    private $needsPrimaryServer;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * @param array $options
     * @param bool $needsPrimaryServer
     * @param OptionsResolver $resolver
     */
    public function __construct(array $options, $needsPrimaryServer, OptionsResolver $resolver)
    {
        $this->options = $options;
        $this->needsPrimaryServer = (bool)$needsPrimaryServer;
        $this->resolver = $resolver;
    }

    public function getOptions(Server $server)
    {
        return $this->resolver->runtimeResolve($server, $this->options);
    }

    public function needsPrimaryServer()
    {
        return $this->needsPrimaryServer;
    }
}