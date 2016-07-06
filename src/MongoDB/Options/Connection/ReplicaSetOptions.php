<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;

final class ReplicaSetOptions implements ConfigurableInterface
{
    const REPLICA_SET = 'replicaSet';
    
    public static function getAll()
    {
        return [
            self::REPLICA_SET,
        ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::REPLICA_SET, ['string']);
    }
}