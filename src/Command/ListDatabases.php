<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Options\CompatibilityResolverInterface;
use Tequila\MongoDB\Options\OptionsResolver;
use Tequila\MongoDB\ServerInfo;

class ListDatabases extends OptionsResolver implements CompatibilityResolverInterface
{
    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return ['listDatabases' => 1];
    }
}