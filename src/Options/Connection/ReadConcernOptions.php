<?php

namespace Tequila\MongoDB\Options\Connection;

use Tequila\MongoDB\Options\OptionsInterface;
use Tequila\MongoDB\Options\OptionsResolver;

class ReadConcernOptions implements OptionsInterface
{
    const READ_CONCERN_LEVEL = 'readConcernLevel';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::READ_CONCERN_LEVEL);
    }
}
