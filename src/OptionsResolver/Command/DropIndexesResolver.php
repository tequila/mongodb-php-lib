<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernCompatibilityTrait;
use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernConfiguratorTrait;
use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DropIndexesResolver extends OptionsResolver implements CompatibilityResolverInterface
{
    use WriteConcernTrait;
    use WriteConcernCompatibilityTrait;
    use WriteConcernConfiguratorTrait;
}