<?php

namespace Tequila\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;

class ReadConcernOptions implements OptionsInterface
{
    const READ_CONCERN_LEVEL = 'readConcernLevel';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::READ_CONCERN_LEVEL);
    }
}
