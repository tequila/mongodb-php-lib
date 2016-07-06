<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;

class WriteConcernOptions implements ConfigurableInterface
{
    const WRITE_CONCERN = 'w';
    const WRITE_CONCERN_TIMEOUT_MS = 'wtimeoutms';
    const JOURNAL = 'journal';
    
    public static function getAll()
    {
        return [
            self::WRITE_CONCERN,
            self::WRITE_CONCERN_TIMEOUT_MS,
            self::JOURNAL,
        ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
        $resolver->setAllowedTypes(self::WRITE_CONCERN, ['integer', 'string']);
        $resolver->setAllowedTypes(self::WRITE_CONCERN_TIMEOUT_MS, ['integer']);
        $resolver->setAllowedTypes(self::JOURNAL, ['bool']);
    }
}