<?php

namespace Tequila\MongoDB\Command\Traits;

use MongoDB\Driver\ReadPreference;

trait ReadPreferenceTrait
{
    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @param ReadPreference $readPreference
     */
    public function setDefaultReadPreference(ReadPreference $readPreference)
    {
        $this->readPreference = $readPreference;
    }
}