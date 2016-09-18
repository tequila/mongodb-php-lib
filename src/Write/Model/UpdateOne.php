<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Write\Options\UpdateOneOptions;
use Tequilla\MongoDB\Util\ValidatorUtils;

class UpdateOne implements WriteModelInterface
{
    use Traits\BulkUpdateTrait;

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