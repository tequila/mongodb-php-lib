<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Util\TypeUtils;
use Tequila\MongoDB\Write\Bulk\BulkWrite;
use Tequila\MongoDB\Write\Options\UpdateOptions;

class ReplaceOne implements WriteModelInterface
{
    use Traits\EnsureValidFilterTrait;
    use Traits\EnsureValidDocumentTrait;
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

        $options = UpdateOptions::resolve($options);
        if (isset($options['multi']) && $options['multi']) {
            throw new InvalidArgumentException(
                'ReplaceOne operation does not allow option "multi" to be true'
            );
        }

        $this->filter = $filter;
        $this->replacement = $replacement;
        $this->options = $options;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->update($this->filter, $this->replacement, $this->options);
    }
}