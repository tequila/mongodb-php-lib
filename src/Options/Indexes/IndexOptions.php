<?php

namespace Tequila\MongoDB\Options\Indexes;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class IndexOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'name',
            'background',
            'unique',
            'partialFilterExpression',
            'sparse',
            'expireAfterSeconds',
            'storageEngine',
            'weights',
            'default_language',
            'language_override',
            'textIndexVersion',
            '2dsphereIndexVersion',
            'bits',
            'min',
            'max',
            'bucketSize',
        ]);

        $numberTypes = ['integer', 'float'];
        $documentTypes = ['array', 'object'];

        $resolver
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('background', 'boolean')
            ->setAllowedTypes('unique', 'boolean')
            ->setAllowedTypes('partialFilterExpression', $documentTypes)
            ->setAllowedTypes('sparse', 'boolean')
            ->setAllowedTypes('expireAfterSeconds', 'integer')
            ->setAllowedTypes('storageEngine', $documentTypes)
            ->setAllowedTypes('weights', $documentTypes)
            ->setAllowedTypes('default_language', 'string')
            ->setAllowedTypes('language_override', 'string')
            ->setAllowedTypes('textIndexVersion', 'integer')
            ->setAllowedTypes('2dsphereIndexVersion', 'integer')
            ->setAllowedTypes('bits', 'integer')
            ->setAllowedTypes('min', $numberTypes)
            ->setAllowedTypes('max', $numberTypes)
            ->setAllowedTypes('bucketSize', $numberTypes);
    }
}