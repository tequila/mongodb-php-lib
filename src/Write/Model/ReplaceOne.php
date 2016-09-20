<?php

namespace Tequilla\MongoDB\Write\Model;

use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Util\TypeUtils;
use Tequilla\MongoDB\Write\Bulk\BulkWrite;
use Tequilla\MongoDB\Write\Options\ReplaceOneOptions;

class ReplaceOne implements WriteModelInterface
{
    use Traits\FilterValidationTrait;
    use Traits\DocumentValidationTrait;
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
        $this->ensureValidFilter($filter);

        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, %s given',
                    TypeUtils::getType($filter)
                )
            );
        }

        $this->ensureValidDocument($replacement);

        $this->filter = $filter;
        $this->replacement = $replacement;
        $this->options = ReplaceOneOptions::getCachedResolver()->resolve($options);
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->replacement, $this->options);
    }
}