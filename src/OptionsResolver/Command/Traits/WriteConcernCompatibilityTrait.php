<?php

namespace Tequila\MongoDB\OptionsResolver\Command\Traits;

use Tequila\MongoDB\CommandOptions;

trait WriteConcernCompatibilityTrait
{
    public function resolveCompatibilities(CommandOptions $options)
    {
        $options->resolveWriteConcern($this->writeConcern);
    }
}