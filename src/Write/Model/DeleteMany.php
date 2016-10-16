<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Write\Options\DeleteOptions;

class DeleteMany implements WriteModelInterface
{
    use Traits\BulkDeleteTrait;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        if (isset($options['limit']) && 1 === $options['limit']) {
            throw new InvalidArgumentException(
                'DeleteMany operation does not allow option "limit" to be set to 1'
            );
        }

        $this->filter = $filter;
        $this->options = DeleteOptions::resolve($options);
    }
}