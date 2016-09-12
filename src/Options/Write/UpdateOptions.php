<?php

namespace Tequilla\MongoDB\Options\Write;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\ConfigurableInterface;

class UpdateOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'upsert',
            'multi',
            'collation',
        ]);

        $resolver->setAllowedTypes('upsert', 'bool');
        $resolver->setAllowedTypes('multi', 'bool');
        $resolver->setDefaults([
            'upsert' => false,
            'multi' => false,
        ]);

        $resolver->setNormalizer('multi', function(Options $options, $multi) {
            if ($multi && $options['upsert']) {
                throw new InvalidArgumentException(
                    'Option "multi" cannot be true if "upsert" option is true'
                );
            }
        });
    }
}