<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\OptionsResolver\BulkWrite\UpdateDocumentResolver;
use Tequila\MongoDB\OptionsResolver\ResolverFactory;
use Tequila\MongoDB\Write\Model\Traits\BulkUpdateTrait;

class UpdateMany implements WriteModelInterface
{
    use BulkUpdateTrait;

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        $update = ResolverFactory::get(UpdateDocumentResolver::class)->resolve($update);
        $options = ['multi' => true] + $options;

        $this->update = new Update($filter, $update, $options);
    }
}