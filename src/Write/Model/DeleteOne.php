<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Write\Model\Traits\BulkDeleteTrait;

class DeleteOne implements WriteModelInterface
{
    use BulkDeleteTrait;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        $options = ['limit' => 1] + $options;
        $this->delete = new Delete($filter, $options);
    }
}