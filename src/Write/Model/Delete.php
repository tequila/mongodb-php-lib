<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\OptionsResolver\BulkWrite\DeleteResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;
use Tequila\MongoDB\Server;
use Tequila\MongoDB\Write\Model\Traits\CheckCompatibilityTrait;

class Delete implements WriteModelInterface
{
    use CheckCompatibilityTrait;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        $this->filter = $filter;
        $this->options = ResolverFactory::get(DeleteResolver::class)->resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk, Server $server)
    {
        $this->checkCompatibility($this->options, $server);

        $bulk->delete($this->filter, $this->options);
    }


}