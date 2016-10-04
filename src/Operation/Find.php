<?php

namespace Tequila\MongoDB\Operation;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\TypeUtil;

class Find implements OperationInterface
{
    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ReadPreference
     */
    private $readPreference;

    /**
     * @param array|object $filter
     * @param array $options
     */
    public function __construct($filter, array $options = [])
    {
        if (!is_array($filter) && !is_object($filter)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$filter must be an array or an object, %s given',
                    TypeUtil::getType($filter)
                )
            );
        }

        $options = Options\FindOptions::resolve($options);
        if (isset($options['readPreference'])) {
            $this->readPreference = $options['readPreference'];
            unset($options['readPreference']);
        }

        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @return \MongoDB\Driver\Cursor
     */
    public function execute(Manager $manager, $databaseName, $collectionName)
    {
        $query = new Query($this->filter, $this->options);

        return $manager->executeQuery(
            $databaseName . '.' . $collectionName,
            $query,
            $this->readPreference
        );
    }
}