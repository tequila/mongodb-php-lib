<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Traits\EnsureValidUpdateTrait;
use Tequila\MongoDB\Write\Options\UpdateOptions;

class UpdateMany implements WriteModelInterface
{
    use EnsureValidUpdateTrait;
    use Traits\BulkUpdateTrait;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $this->ensureValidUpdate($update);

        $options += ['multi' => true];

        $options = UpdateOptions::resolve($options);
        if (isset($options['multi']) && false === $options['multi']) {
            throw new InvalidArgumentException(
                'UpdateMany operation does not allow option "multi" to be false'
            );
        }

        $this->filter = $filter;
        $this->update = $update;
        $this->options = $options;
    }
}