<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\OptionsResolver\BulkWrite\UpdateResolver;
use Tequila\MongoDB\WriteModelInterface;

class Update implements WriteModelInterface
{
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
     * @param array        $filter
     * @param array|object $update
     * @param array        $options
     */
    public function __construct(array $filter, $update, array $options = [])
    {
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateResolver::resolveStatic($options);
    }

    /**
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}
