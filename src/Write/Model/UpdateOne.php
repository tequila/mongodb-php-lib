<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\OptionsResolver\BulkWrite\UpdateDocumentResolver;
use Tequila\MongoDB\Write\Model\Traits\BulkUpdateTrait;
use Tequila\MongoDB\WriteModelInterface;

class UpdateOne implements WriteModelInterface
{
    use BulkUpdateTrait;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $update = UpdateDocumentResolver::resolveStatic($update);
        $options = ['multi' => false] + $options;

        $this->update = new Update($filter, $update, $options);
    }
}