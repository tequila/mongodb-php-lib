<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateOptions;
use WriteModel\BulkUpdateTrait;

class Update implements WriteModelInterface
{
    use BulkUpdateTrait;
    use ValidateFilterTrait;

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
}