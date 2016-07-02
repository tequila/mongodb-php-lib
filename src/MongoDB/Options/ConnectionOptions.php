<?php

namespace Tequilla\MongoDB\Options;

final class ConnectionOptions implements OptionsInterface
{
    const SSL = 'ssl';
    const CONNECT_TIMEOUT_MS = 'connectTimeoutMS';
    const SOCKET_TIMEOUT_MS = 'socketTimeoutMS';
    
    public static function getAll()
    {
        return [
            self::SSL,
            self::CONNECT_TIMEOUT_MS,
            self::SOCKET_TIMEOUT_MS,
        ];
    }
}