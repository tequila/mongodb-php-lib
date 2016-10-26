<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Traits\EnsureValidUpdateTrait;
use Tequila\MongoDB\Write\Model\Traits\BulkUpdateTrait;

class UpdateMany implements WriteModelInterface
{
    use BulkUpdateTrait;
    use EnsureValidUpdateTrait;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $this->ensureValidUpdate($update);
        $options = ['multi' => true] + self::resolve($options);

        $this->update = new Update($filter, $update, $options);
    }
}