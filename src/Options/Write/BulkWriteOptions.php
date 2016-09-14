<?php

namespace Tequilla\MongoDB\Options\Write;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

class BulkWriteOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'bypassDocumentValidation',
            'ordered',
        ]);

        $resolver
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('ordered', 'bool');

        $resolver
            ->setDefault('bypassDocumentValidation', true)
            ->setDefault('ordered', true);
    }
}