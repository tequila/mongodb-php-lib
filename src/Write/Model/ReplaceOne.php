<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Write\Model\Traits\BulkUpdateTrait;
use function Tequila\MongoDB\ensureValidDocument;
use Tequila\MongoDB\WriteModelInterface;

class ReplaceOne implements WriteModelInterface
{
    use BulkUpdateTrait;

    /**
     * @param $filter
     * @param array|object $replacement
     * @param array        $options
     */
    public function __construct(array $filter, $replacement, array $options = [])
    {
        if (!is_array($replacement) && !is_object($replacement)) {
            throw new InvalidArgumentException(
                sprintf(
                    '$replacement must be an array or an object, "%s" given.',
                    \Tequila\MongoDB\getType($replacement)
                )
            );
        }

        try {
            ensureValidDocument($replacement);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid $replacement document: %s', $e->getMessage())
            );
        }

        $options = ['multi' => false] + $options;
        $this->update = new Update($filter, $replacement, $options);
    }
}
