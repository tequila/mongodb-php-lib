<?php

namespace Tequila\MongoDB\Command;

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