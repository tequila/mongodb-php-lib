<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\OptionsResolver\BulkWrite\UpdateResolver;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Server;
use Tequila\MongoDB\Write\Model\Traits\CheckCompatibilityTrait;

class Update implements WriteModelInterface
{
    use CheckCompatibilityTrait;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $this->filter = $filter;
        $this->update = $update;
        $this->options = OptionsResolver::get(UpdateResolver::class)->resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk, Server $server)
    {
        $this->checkCompatibility($this->options, $server);

        $bulk->update($this->filter, $this->update, $this->options);
    }
}