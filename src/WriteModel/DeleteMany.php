<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\DeleteManyOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class DeleteMany
{
    use BulkDeleteTrait;

    /**
     * @param array|object $filter
     * @param array $options
     */
    public function __construct($filter, array $options = [])
    {
        ValidatorUtils::ensureValidFilter($filter);
        $this->filter = $filter;
        $this->options = DeleteManyOptions::getCachedResolver()->resolve($options);
    }
}