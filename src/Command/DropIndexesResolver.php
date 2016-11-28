<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\WriteConcernCompatibilityTrait;
use Tequila\MongoDB\Command\Traits\WriteConcernTrait;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\OptionsResolver;

class DropIndexesResolver extends OptionsResolver implements CompatibilityResolverInterface
{
    use WriteConcernTrait;
    use WriteConcernCompatibilityTrait;
}