<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Options\Write\UpdateOptions;

class Update implements WriteModelInterface
{
    use ValidateFilterTrait;

    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array|object
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * Update constructor.
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        self::validateFilter($filter);
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}