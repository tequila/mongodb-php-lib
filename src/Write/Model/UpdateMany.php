<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\UpdateManyOptions;

class UpdateMany implements WriteModelInterface
{
    use Traits\FilterValidationTrait;
    use Traits\UpdateValidationTrait;
    use Traits\BulkUpdateTrait;
    use Traits\UpdateValidationTrait;

    /**
     * Update constructor.
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        $this->ensureValidFilter($filter);
        $this->ensureValidUpdate($update);

        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateManyOptions::getCachedResolver()->resolve($options);
    }
}