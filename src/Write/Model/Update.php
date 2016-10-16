<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Write\Options\UpdateOptions;

class Update implements WriteModelInterface
{
    use Traits\BulkUpdateTrait;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOptions::resolve($options);
    }
}