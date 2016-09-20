<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\UpdateManyOptions;

class UpdateMany implements WriteModelInterface
{
    use Traits\EnsureValidFilterTrait;
    use Traits\EnsureValidUpdateTrait;
    use Traits\BulkUpdateTrait;
    use Traits\EnsureValidUpdateTrait;

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