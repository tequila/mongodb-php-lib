<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\CommandOptions;

interface CompatibilityResolverInterface
{
    /**
     * @param CommandOptions $options
     * @void
     */
    public function resolveCompatibilities(CommandOptions $options);
}