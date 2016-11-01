<?php

namespace Tequila\MongoDB\Command;

use Tequila\MongoDB\Options\CompatibilityResolver;
use Tequila\MongoDB\Options\WritingCommandOptions;
use Tequila\MongoDB\Command\Traits\PrimaryServerTrait;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\ServerInfo;

class CreateIndexes implements CommandInterface
{
    use PrimaryServerTrait;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $collectionName
     * @param Index[] $indexes
     * @param array $options
     */
    public function __construct($collectionName, array $indexes, array $options = [])
    {
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes array cannot be empty');
        }

        $compiledIndexes = array_map(
            function (Index $index) {
                return $index->toArray();
            },
            $indexes
        );

        $this->options = [
                'createIndexes' => (string)$collectionName,
                'indexes' => $compiledIndexes,
            ] + WritingCommandOptions::resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions(ServerInfo $serverInfo)
    {
        return CompatibilityResolver::getInstance(
            $serverInfo,
            $this->options,
            ['writeConcern']
        )->resolve();
    }
}