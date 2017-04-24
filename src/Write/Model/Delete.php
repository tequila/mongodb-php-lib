<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\OptionsResolver\BulkWrite\DeleteResolver;
use Tequila\MongoDB\WriteModelInterface;

class Delete implements WriteModelInterface
{
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
        $this->options = DeleteResolver::resolveStatic($options);
    }

    /**
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->delete($this->filter, $this->options);
    }
}
