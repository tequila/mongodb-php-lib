<?php

namespace Tequilla\MongoDB\Options\Write;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\ConfigurableInterface;
use Tequilla\MongoDB\Options\Traits\CachedResolverTrait;

class DeleteManyOptions implements ConfigurableInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        DeleteOptions::configureOptions($resolver);
        $resolver->setDefault('limit', 0);
        $resolver->setNormalizer('limit', function (Options $options, $limit) {
            if (1 === $limit) {
                throw new InvalidArgumentException(
                    'Option "limit" cannot be set to 1 for DeleteMany operation. If you want to delete one document - use DeleteOne'
                );
            }

            return $limit;
        });
    }
}