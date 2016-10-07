<?php

namespace Tequila\MongoDB\Command;

use MongoDB\Driver\Manager;
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
     * @param string $databaseName
     * @param string $collectionName
     * @param Index[] $indexes
     */
    public function __construct($databaseName, $collectionName, array $indexes)
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
    }

    public function execute(Manager $manager)
    {
        $options = [
            'createIndexes' => $this->collectionName,
            'indexes' => $this->indexes,
        ];

        return $this->executeOnPrimaryServer($manager, $this->databaseName, $options);
    }
}