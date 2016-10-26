<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class BulkWriteOptions
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

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

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }
}