<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\DeleteManyOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class DeleteMany implements WriteModelInterface
{
    use Traits\BulkDeleteTrait;

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