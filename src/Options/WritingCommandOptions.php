<?php

namespace Tequila\MongoDB\Options;

use MongoDB\Driver\WriteConcern;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Traits\CachedResolverTrait;

class WritingCommandOptions
{
    use CachedResolverTrait {
        resolve as privateResolve;
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('writeConcern');
        $resolver->setAllowedTypes('writeConcern', WriteConcern::class);
        $resolver->setNormalizer('writeConcern', function (Options $options, WriteConcern $writeConcern) {
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

    public static function resolve(array $options)
    {
        return self::privateResolve($options);
    }
}