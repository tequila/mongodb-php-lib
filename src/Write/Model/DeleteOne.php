<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Write\Options\DeleteOptions;

class DeleteOne implements WriteModelInterface
{
    use Traits\BulkDeleteTrait;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        if (isset($options['limit']) && 0 === $options['limit']) {
            throw new InvalidArgumentException(
                'DeleteOne operation does not allow option "limit" to be set to 0'
            );
        }

        $this->filter = $filter;
        $this->options = DeleteOptions::resolve($options);
    }
}