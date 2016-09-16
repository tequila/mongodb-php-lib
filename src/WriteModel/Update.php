<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class Update implements WriteModelInterface
{
    use BulkUpdateTrait;

    /**
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        ValidatorUtils::ensureValidFilter($filter);
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOptions::getCachedResolver()->resolve($options);
    }
}