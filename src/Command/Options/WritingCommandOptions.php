<?php

namespace Tequila\MongoDB\Command\Options;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class WritingCommandOptions implements OptionsInterface
{
    use CachedResolverTrait;

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('writeConcern');
        $resolver->setAllowedTypes('writeConcern', WriteConcern::class);
    }
}