<?php

namespace Tequilla\MongoDB\WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\Write\ReplaceOneOptions;
use Tequilla\MongoDB\Util\StringUtils;
use Tequilla\MongoDB\Util\TypeUtils;

class ReplaceOne implements WriteModelInterface
{
    use ValidateFilterTrait;

    /**
     * @var array|object
     */
    private $filter;

    /**
     * @var array|object
     */
    private $replacement;

    /**
     * @var array
     */
    private $options;

    /**
     * @param $filter
     * @param $replacement
     * @param array $options
     */
    public function __construct($filter, $replacement, array $options = [])
    {
        self::validateFilter($filter);
        self::validateReplacement($replacement);

        $this->filter = $filter;
        $this->replacement = $replacement;
        $this->options = ReplaceOneOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->replacement, $this->options);
    }

    private static function validateReplacement($replacement)
    {
        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, %s given',
                    TypeUtils::getType($replacement)
                )
            );
        }

        $replacement = TypeUtils::ensureArray($replacement);

        if (empty($replacement)) {
            throw new InvalidArgumentException('$replacement cannot be empty');
        }

        array_walk_recursive($replacement, function($value, $fieldName) {
            StringUtils::ensureValidDocumentFieldName($fieldName);
        });
    }
}