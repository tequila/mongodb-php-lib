<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Options\Write\UpdateOneOptions;

class UpdateOne implements WriteModelInterface
{
    use ValidateFilterTrait;
    use ValidateUpdateTrait;

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
        self::validateUpdate($update);

        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOneOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }
}