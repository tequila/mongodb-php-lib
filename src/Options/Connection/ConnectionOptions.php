<?php

namespace Tequila\MongoDB\Options\Connection;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\Options\Traits\CachedResolverTrait;

class ConnectionOptions implements OptionsInterface
{
    use CachedResolverTrait;

    const SSL = 'ssl';
    const CONNECT_TIMEOUT_MS = 'connectTimeoutMS';
    const SOCKET_TIMEOUT_MS = 'socketTimeoutMS';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            self::SSL,
            self::CONNECT_TIMEOUT_MS,
            self::SOCKET_TIMEOUT_MS,
        ]);

        $resolver
            ->setAllowedTypes(self::SSL, ['bool'])
            ->setAllowedTypes(self::CONNECT_TIMEOUT_MS, 'integer')
            ->setAllowedTypes(self::SOCKET_TIMEOUT_MS, 'integer');
        
        AuthenticationOptions::configureOptions($resolver);
        ReadConcernOptions::configureOptions($resolver);
        ReadPreferenceOptions::configureOptions($resolver);
        ReplicaSetOptions::configureOptions($resolver);
        WriteConcernOptions::configureOptions($resolver);
    }
}