<?php

namespace Tequila\MongoDB\Command\Options;

use Tequila\MongoDB\Command\FindAndModify;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class FindOneAndUpdateOptions implements OptionsInterface
{
    use CachedResolverTrait {
        CachedResolverTrait::resolve as resolveOptions;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        WritingCommandOptions::configureOptions($resolver);

        $resolver->setDefined([
            'bypassDocumentValidation',
            'maxTimeMS',
            'projection',
            'returnDocument',
            'sort',
            'upsert',
            'collation',
        ]);

        $resolver->setAllowedValues(
            'returnDocument',
            [
                FindAndModify::RETURN_DOCUMENT_BEFORE,
                FindAndModify::RETURN_DOCUMENT_AFTER,
            ]
        );

        $resolver->setDefault('returnDocument', FindAndModify::RETURN_DOCUMENT_BEFORE);
    }

    public static function resolve(array $options = [])
    {
        $options = self::resolveOptions($options);

        if (isset($options['projection'])) {
            $options['fields'] = $options['projection'];
            unset($options['projection']);
        }

        if (FindAndModify::RETURN_DOCUMENT_AFTER === $options['returnDocument']) {
            $options['new'] = true;
            unset($options['returnDocument']);
        }

        return $options;
    }
}