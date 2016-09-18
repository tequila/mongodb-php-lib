<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\DeleteOneOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class DeleteOne implements WriteModelInterface
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
        $this->options = DeleteOneOptions::getCachedResolver()->resolve($options);
    }
}