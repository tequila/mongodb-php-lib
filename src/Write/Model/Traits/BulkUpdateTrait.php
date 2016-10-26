<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\Write\Model\Update;

trait BulkUpdateTrait
{
    use CachedResolverTrait;

    /**
     * @var Update
     */
    private $update;

    /**
     * @see \Tequila\MongoDB\Write\Model\WriteModelInterface::writeToBulk()
     *
     * @param \Tequila\MongoDB\BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $this->update->writeToBulk($bulk);
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);

        $resolver->setDefined('upsert');
        $resolver->setAllowedTypes('upsert', 'bool');
    }
}