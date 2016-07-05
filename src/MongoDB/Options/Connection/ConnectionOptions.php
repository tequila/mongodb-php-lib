<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

final class ConnectionOptions implements ConfigurableClassInterface
{
    const SSL = 'ssl';
    const CONNECT_TIMEOUT_MS = 'connectTimeoutMS';
    const SOCKET_TIMEOUT_MS = 'sockettimeoutms';
    
    public static function getAll()
    {
        return [
            self::SSL,
            self::CONNECT_TIMEOUT_MS,
            self::SOCKET_TIMEOUT_MS,
        ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::SSL, ['bool']);
        $resolver->setAllowedTypes(self::CONNECT_TIMEOUT_MS, 'integer');
        $resolver->setAllowedTypes(self::SOCKET_TIMEOUT_MS, 'integer');
        
        AuthenticationOptions::configureOptions($resolver);
        ReadConcernOptions::configureOptions($resolver);
        ReadPreferenceOptions::configureOptions($resolver);
        ReplicaSetOptions::configureOptions($resolver);
        WriteConcernOptions::configureOptions($resolver);
    }
}