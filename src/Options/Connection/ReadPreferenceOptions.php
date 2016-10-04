<?php

namespace Tequila\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\OptionsInterface;

class ReadPreferenceOptions implements OptionsInterface
{
    const READ_PREFERENCE = 'readPreference';
    const READ_PREFERENCE_TAGS = 'readPreferenceTags';

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            self::READ_PREFERENCE,
            self::READ_PREFERENCE_TAGS,
        ]);

        $resolver->setAllowedValues(self::READ_PREFERENCE, [
            'primary',
            'primaryPreferred',
            'secondary',
            'secondaryPreferred',
            'nearest',
        ]);

        $resolver->setAllowedTypes(self::READ_PREFERENCE_TAGS, ['string', 'array']);
    }
}