<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateManyOptions;
use WriteModel\BulkUpdateTrait;

class UpdateMany implements WriteModelInterface
{
    use BulkUpdateTrait;
    use ValidateFilterTrait;
    use ValidateUpdateTrait;

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
        $this->options = UpdateManyOptions::getCachedResolver()->resolve($options);
    }
}