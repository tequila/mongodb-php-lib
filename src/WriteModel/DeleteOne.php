<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\DeleteOneOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class DeleteOne implements WriteModelInterface
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
        $this->options = DeleteOneOptions::getCachedResolver()->resolve($options);
    }
}