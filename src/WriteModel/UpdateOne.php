<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\Write\UpdateOneOptions;
use Tequilla\MongoDB\Util\TypeUtils;

class UpdateOne implements WriteModelInterface
{
    use ValidateFilterTrait;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $update;

    /**
     * @var array
     */
    private $options;

    /**
     * Update constructor.
     * @param array $filter
     * @param array $update
     * @param array $options
     */
    public function __construct(array $filter, array $update, array $options = [])
    {
        self::validateFilter($filter);
        self::validateUpdate($update);
        $this->filter = $filter;
        $this->update = $update;
        $this->options = UpdateOneOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->update, $this->options);
    }

    private static function validateUpdate($update)
    {
        if (!is_array($update) && !is_object($update)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$update must be an array or an object, %s given',
                    TypeUtils::getType($update)
                )
            );
        }

        $update = TypeUtils::convertToArray($update);

        if (empty($update)) {
            throw new InvalidArgumentException('$update cannot be empty');
        }
    }
}