<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\Exception\InvalidArgumentException;

class ReplaceOne extends Update
{
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
            \Tequila\MongoDB\ensureValidDocument($replacement);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid $replacement document: %s', $e->getMessage())
            );
        }

        $options = ['multi' => false] + $options;

        parent::__construct($filter, $replacement, $options);
    }
}
