<?php

namespace Tequila\MongoDB\Command\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class AggregateOptions implements OptionsInterface
{
    use CachedResolverTrait {
        CachedResolverTrait::resolve as resolveOptions;
    }

    /**
     * @param array $options
     * @return array
     */
    public static function resolve(array $options)
    {
        $options = self::resolveOptions($options);
        unset($options['batchSize']);

        return $options;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'allowDiskUse',
            'batchSize',
            'bypassDocumentValidation',
            'cursor',
            'maxTimeMS',
            'readConcern',
            'readPreference',
        ]);

        $resolver
            ->setAllowedTypes('allowDiskUse', 'bool')
            ->setAllowedTypes('batchSize', 'integer')
            ->setAllowedTypes('bypassDocumentValidation', 'bool')
            ->setAllowedTypes('cursor', ['array', 'object'])
            ->setAllowedTypes('maxTimeMS', 'integer')
            ->setAllowedTypes('readConcern', ReadConcern::class)
            ->setAllowedTypes('readPreference', ReadPreference::class);

        $resolver->setDefault('cursor', new \stdClass());

        $resolver->setNormalizer('cursor', function(Options $options, $cursorOptions) {
            $cursorOptions = (array)$cursorOptions;
            if (isset($options['batchSize'])) {
                $cursorOptions['batchSize'] = $options['batchSize'];
            }

            return (object)$cursorOptions;
        });

        $resolver->setNormalizer('readConcern', function(Options $options, ReadConcern $readConcern) {
            if (null === $readConcern->getLevel()) {
                return null; // mark this option to be deleted during call to resolve()
            }

            return $readConcern;
        });
    }
}