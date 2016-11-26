<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\ReadPreference;

trait ReadPreferenceTrait
{
    /**
     * @param array $options
     * @param ReadPreference $defaultReadPreference
     * @return ReadPreference
     */
    public function resolveReadPreference(array $options, ReadPreference $defaultReadPreference)
    {
        return isset($options['readPreference']) ? $options['readPreference'] : $defaultReadPreference;
    }
}