<?php

namespace Tequila\MongoDB\Write\Model\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Options\CollationOptions;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Traits\CachedResolverTrait;
use Tequila\MongoDB\Write\Model\Delete;

trait BulkDeleteTrait
{
    use CachedResolverTrait;

    /**
     * @var Delete
     */
    private $delete;

    /**
     * @see \Tequila\MongoDB\Write\Model\WriteModelInterface::writeToBulk()
     *
     * @param BulkWrite $bulk
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $this->delete->writeToBulk($bulk);
    }

    private static function configureOptions(OptionsResolver $resolver)
    {
        CollationOptions::configureOptions($resolver);
    }
}