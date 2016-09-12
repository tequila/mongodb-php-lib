<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Options\Write\UpdateManyOptions;

class UpdateMany implements WriteModelInterface
{
    use ValidateFilterTrait;
    use ValidateUpdateTrait;

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
     * Update constructor.
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        self::validateFilter($filter);
        self::validateUpdate($update);

        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateManyOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}