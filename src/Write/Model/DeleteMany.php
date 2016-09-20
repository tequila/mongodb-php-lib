<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\DeleteManyOptions;

class DeleteMany implements WriteModelInterface
{
    use Traits\EnsureValidFilterTrait;
    use Traits\BulkDeleteTrait;

    /**
     * @param array|object $filter
     * @param array $options
     */
    public function __construct($filter, array $options = [])
    {
        $this->ensureValidFilter($filter);
        $this->filter = $filter;
        $this->options = DeleteManyOptions::getCachedResolver()->resolve($options);
    }
}