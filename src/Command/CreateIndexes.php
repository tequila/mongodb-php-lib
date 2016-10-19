<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
use Tequila\MongoDB\Command\Options\WritingCommandOptions;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\Util\TypeUtil;

class CreateIndexes implements CommandInterface
{
    use Traits\PrimaryServerTrait;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $indexes = [];

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param Index[] $indexes
     * @param array $options
     */
    public function __construct($databaseName, $collectionName, array $indexes, array $options = [])
    {
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes array cannot be empty');
        }

        foreach ($indexes as $i => $index) {
            if (!$index instanceof Index) {
                throw new InvalidArgumentException(
                    sprintf(
                        '$indexes[%d] must be an Index instance, %s given',
                        $i,
                        TypeUtil::getType($index)
                    )
                );
            }

            $this->indexes[] = ['key' => $index->getKey()] + $index->getOptions();
        }

        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->options = WritingCommandOptions::resolve($options);
    }

    public function execute(Manager $manager)
    {
        $options = [
            'createIndexes' => $this->collectionName,
            'indexes' => $this->indexes,
        ] + $this->options;

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}