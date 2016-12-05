<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use MongoDB\Driver\ReadPreference;

interface ReadPreferenceResolverInterface
{
    /**
     * @param array $options
     * @param ReadPreference $defaultReadPreference
     * @return ReadPreference
     */
    public function resolveReadPreference(array $options, ReadPreference $defaultReadPreference);
}