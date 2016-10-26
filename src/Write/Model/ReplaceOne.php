<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Traits\EnsureValidDocumentTrait;
use Tequila\MongoDB\Util\TypeUtil;
use Tequila\MongoDB\Write\Model\Traits\BulkUpdateTrait;

class ReplaceOne implements WriteModelInterface
{
    use BulkUpdateTrait;
    use EnsureValidDocumentTrait;

    /**
     * @param $filter
     * @param $replacement
     * @param array $options
     */
    public function __construct(array $filter, $replacement, array $options = [])
    {
        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, %s given',
                    TypeUtil::getType($replacement)
                )
            );
        }

        $this->ensureValidDocument($replacement);

        $options = ['multi' => false] + self::resolve($options);
        $this->update = new Update($filter, $replacement, $options);
    }
}