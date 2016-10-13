<?php

namespace Tequila\MongoDB\Command\Options;

use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class FindAndModifyOptions implements OptionsInterface
{
    use CachedResolverTrait {
        CachedResolverTrait::resolve as resolveOptions;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'sort',
            'remove',
            'update',
            'new',
            'fields',
            'upsert',
            'bypassDocumentValidation',
            'writeConcern',
            'maxTimeMS',
        ]);

        $documentTypes = ['array', 'object'];

        $resolver
            ->setAllowedTypes('sort', $documentTypes)
            ->setAllowedTypes('remove', 'bool')
            ->setAllowedTypes('update', $documentTypes)
            ->setAllowedTypes('new', 'bool')
            ->setAllowedTypes('fields', $documentTypes)
            ->setAllowedTypes('upsert', 'bool')
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('writeConcern', WriteConcern::class)
            ->setAllowedTypes('maxTimeMS', 'integer');

        $resolver->setDefault('remove', false);

        $resolver->setNormalizer('remove', function(Options $options, $remove) {
            if (true === $remove) {
                foreach (['update', 'new', 'upsert'] as $prohibitedOption) {
                    if (isset($options[$prohibitedOption])) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Option "%s" is not allowed when option "remove" is set to true',
                                $prohibitedOption
                            )
                        );
                    }
                }
            } else {
                if (!isset($options['update'])) {
                    throw new InvalidArgumentException(
                        'Option "update" is required when option "remove" is set to false or not exists'
                    );
                }
            }
        });

        $resolver->setNormalizer('writeConcern', function(Options $options, WriteConcern $writeConcern) {
            $writeConcernOptions = [];

            if (null !== ($w = $writeConcern->getW())) {
                $writeConcernOptions['w'] = $w;
            }

            if (null !== ($j = $writeConcern->getJournal())) {
                $writeConcernOptions['j'] = $j;
            }

            if (null !== ($wTimeout = $writeConcern->getWtimeout())) {
                $writeConcernOptions['wtimeout'] = $wTimeout;
            }

            return (object)$writeConcernOptions;
        });
    }
}