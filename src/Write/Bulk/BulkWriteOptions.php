<?php

namespace Tequila\MongoDB\Write\Bulk;

use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class BulkWriteOptions implements ConfigurableInterface
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

        $resolver
            ->setDefault('bypassDocumentValidation', true)
            ->setDefault('ordered', true);
    }

    /**
     * @return \string[]
     */
    public static function getDefinedOptions()
    {
        return self::getResolver()->getDefinedOptions();
    }
}