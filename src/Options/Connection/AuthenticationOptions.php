<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;

class AuthenticationOptions implements  ConfigurableInterface
{
    const AUTH_SOURCE = 'authSource';
    const AUTH_MECHANISM = 'authMechanism';
    
    public static function getAll()
    {
        return [
            self::AUTH_SOURCE,
            self::AUTH_MECHANISM,
        ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::AUTH_SOURCE, ['string']);
        $resolver->setAllowedValues(self::AUTH_MECHANISM, [
            'SCRAM-SHA-1',
            'MONGODB-CR',
            'MONGODB-X509',
            'GSSAPI',
            'PLAIN',
        ]);
    }
}