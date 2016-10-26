<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ServerInfo;

class FindOneAndReplace implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var FindOneAndUpdate
     */
    private $findOneAndUpdate;

    /**
     * @param string $collectionName
     * @param array $filter
     * @param array|object $replacement
     * @param array $options
     */
    public function __construct($collectionName, array $filter, $replacement, array $options = [])
    {
        $this->findOneAndUpdate = new FindOneAndUpdate(
            $collectionName,
            $filter,
            $replacement,
            $options
        );
    }

    public function getOptions(ServerInfo $serverInfo)
    {
        return $this->findOneAndUpdate->getOptions($serverInfo);
    }
}