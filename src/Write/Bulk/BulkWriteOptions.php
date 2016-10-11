<?php

namespace Tequila\MongoDB\Write\Bulk;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class BulkWriteOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'bypassDocumentValidation',
            'ordered',
            'writeConcern',
        ]);

        $resolver
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('ordered', 'bool')
            ->setAllowedTypes('writeConcern', WriteConcern::class);
    }

    /**
     * @return \string[]
     */
    public static function getDefinedOptions()
    {
        return self::getResolver()->getDefinedOptions();
    }
}