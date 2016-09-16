<?php

namespace Tequilla\MongoDB\WriteModel;

use Tequilla\MongoDB\Options\Write\UpdateOneOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class UpdateOne implements WriteModelInterface
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
        $this->options = UpdateOneOptions::getCachedResolver()->resolve($options);
    }
}