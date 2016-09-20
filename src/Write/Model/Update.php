<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\UpdateOptions;

class Update implements WriteModelInterface
{
    use Traits\FilterValidationTrait;
    use Traits\BulkUpdateTrait;
    use Traits\UpdateValidationTrait;

    /**
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        $this->ensureValidFilter($filter);
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOptions::getCachedResolver()->resolve($options);
    }
}