<?php

namespace Tequila\MongoDB\Write\Model;

class UpdateOne extends Update
{
    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        \Tequila\MongoDB\ensureValidUpdate($update);
        $options = ['multi' => false] + $options;

        parent::__construct($filter, $update, $options);
    }
}
