<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\ReadPreference;

interface ReadPreferenceAwareInterface
{
    public function setDefaultReadPreference(ReadPreference $readPreference);
}