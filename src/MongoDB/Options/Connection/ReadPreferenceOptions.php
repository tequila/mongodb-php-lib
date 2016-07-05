<?php

namespace Tequilla\MongoDB\Options\Connection;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

final class ReadPreferenceOptions implements ConfigurableClassInterface
{
    const READ_PREFERENCE = 'readPreference';
    const READ_PREFERENCE_TAGS = 'readPreferenceTags';

    public static function getAll()
    {
        return [
            self::READ_PREFERENCE,
            self::READ_PREFERENCE_TAGS,
        ];
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(self::getAll());
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