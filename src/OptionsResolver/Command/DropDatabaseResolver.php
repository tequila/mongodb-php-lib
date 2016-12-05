<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernCompatibilityTrait;
use Tequila\MongoDB\OptionsResolver\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\OptionsResolver\Command\CompatibilityResolverInterface;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;

class DropDatabaseResolver extends OptionsResolver implements
    WriteConcernAwareInterface,
    CompatibilityResolverInterface
{
    use WriteConcernTrait;
    use WriteConcernCompatibilityTrait;
}