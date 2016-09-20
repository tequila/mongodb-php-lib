<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\DeleteOneOptions;

class DeleteOne implements WriteModelInterface
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
        $this->options = DeleteOneOptions::getCachedResolver()->resolve($options);
    }
}