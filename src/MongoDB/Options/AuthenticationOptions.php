<?php

namespace Tequilla\MongoDB\Options;

class AuthenticationOptions implements OptionsInterface
{
    const AUTH_SOURCE = 'authSource';
    const AUTH_MECHANISM = 'authMechanism';
    const GSSAPI_SERVICE_NAME = 'gssapiServiceName';
    
    public static function getAll()
    {
        return [
            self::AUTH_SOURCE,
            self::AUTH_MECHANISM,
            self::GSSAPI_SERVICE_NAME,
        ];
    }
}