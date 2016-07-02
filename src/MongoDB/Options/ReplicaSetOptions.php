<?php

namespace Tequilla\MongoDB\Options;

final class ReplicaSetOptions implements OptionsInterface
{
    const REPLICA_SET = 'replicaSet';
    
    public static function getAll()
    {
        return [
            self::REPLICA_SET,
        ];
    }
}