<?php

namespace Tequila\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;

class ReplicaSetOptions implements ConfigurableInterface
{
    const REPLICA_SET = 'replicaSet';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(self::REPLICA_SET)
            ->setAllowedTypes(self::REPLICA_SET, ['string']);
    }
}