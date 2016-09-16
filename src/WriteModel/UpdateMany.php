<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateManyOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class UpdateMany implements WriteModelInterface
{
    use BulkUpdateTrait;

    /**
     * Update constructor.
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        ValidatorUtils::ensureValidFilter($filter);
        ValidatorUtils::ensureValidUpdate($update);

        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateManyOptions::getCachedResolver()->resolve($options);
    }
}