<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateOneOptions;
use WriteModel\BulkUpdateTrait;

class UpdateOne implements WriteModelInterface
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
        $this->options = UpdateOneOptions::getCachedResolver()->resolve($options);
    }
}