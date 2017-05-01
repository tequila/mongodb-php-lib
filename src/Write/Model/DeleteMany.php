<?php

namespace Tequila\MongoDB\Write\Model;

class DeleteMany extends Delete
{
    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        $options = ['limit' => 0] + $options;
        parent::__construct($filter, $options);
    }
}
