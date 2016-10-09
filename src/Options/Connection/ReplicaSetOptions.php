<?php

namespace Tequila\MongoDB\Options\Connection;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;

class ReplicaSetOptions implements OptionsInterface
{
    const REPLICA_SET = 'replicaSet';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(self::REPLICA_SET)
            ->setAllowedTypes(self::REPLICA_SET, ['string']);
    }
}