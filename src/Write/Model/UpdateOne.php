<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Traits\EnsureValidUpdateTrait;
use Tequila\MongoDB\Write\Options\UpdateOptions;

class UpdateOne implements WriteModelInterface
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

        $options = UpdateOptions::resolve($options);
        if (isset($options['multi']) && $options['multi']) {
            throw new InvalidArgumentException(
                'UpdateOne operation does not allow option "multi" to be true'
            );
        }

        $this->filter = $filter;
        $this->update = $update;
        $this->options = $options;
    }
}