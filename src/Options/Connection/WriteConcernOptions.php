<?php

namespace Tequila\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Tequila\MongoDB\Options\ConfigurableInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\get_type;

class WriteConcernOptions implements ConfigurableInterface
{
    const WRITE_CONCERN = 'w';
    const WRITE_CONCERN_TIMEOUT_MS = 'wtimeoutMS';
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

        $resolver->setNormalizer(self::WRITE_CONCERN_TIMEOUT_MS, function(Options $options, $value) {
            if (!isset($options[self::WRITE_CONCERN]) || $options[self::WRITE_CONCERN] <= 1) {
                throw new InvalidArgumentException(
                    'Option "wtimeoutMS" will not get applied until "w" > 1, "w" = majority, or tag sets are used.'
                );
            }

            return $value;
        });
    }
}
