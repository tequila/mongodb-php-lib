<?php

namespace Tequila\MongoDB\Write\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class DeleteOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['limit', 'collation']);
        $resolver->setAllowedValues('limit', [0, 1]);
    }
}