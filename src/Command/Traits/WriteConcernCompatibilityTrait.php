<?php

namespace Tequila\MongoDB\Command\Traits;

use Tequila\MongoDB\Options\ServerCompatibleOptions;

trait WriteConcernCompatibilityTrait
{
    public function resolveCompatibilities(ServerCompatibleOptions $options)
    {
        $options->checkWriteConcern($this->writeConcern);
    }
}