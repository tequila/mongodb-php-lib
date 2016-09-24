<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Write\Options\UpdateOptions;

class UpdateMany implements WriteModelInterface
{
    use Traits\EnsureValidFilterTrait;
    use Traits\EnsureValidUpdateTrait;
    use Traits\BulkUpdateTrait;

    /**
     * @param array|object $filter
     * @param array|object $update
     * @param array $options
     */
    public function __construct($filter, $update, array $options = [])
    {
        $this->ensureValidFilter($filter);
        $this->ensureValidUpdate($update);

        $options += ['multi' => true];

        $options = UpdateOptions::resolve($options);
        if (isset($options['multi']) && !$options['multi']) {
            throw new InvalidArgumentException(
                'UpdateMany operation does not allow option "multi" to be false'
            );
        }

        $this->filter = $filter;
        $this->update = $update;
        $this->options = $options;
    }
}