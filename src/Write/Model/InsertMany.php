<?php

namespace Tequila\MongoDB\Write\Model;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\WriteModelInterface;
use function Tequila\MongoDB\ensureValidDocument;
use function Tequila\MongoDB\getType;

class InsertMany implements WriteModelInterface
{
    /**
     * @var array
     */
    private $documents = [];

    /**
     * @param array $documents
     */
    public function __construct(array $documents)
    {
        foreach ($documents as $position => $document) {
            if (!is_array($document) && !is_object($document)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Each document must be an array or an object, %s given in $documents[%s].',
                        getType($document),
                        $position
                    )
                );
            }

            try {
                ensureValidDocument($document);
            } catch(InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid document at $documents[%s]: %s',
                        $position,
                        $e->getMessage()
                    )
                );
            }
        }

        $this->documents = $documents;
    }

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        foreach ($this->documents as $document) {
            $bulk->insert($document);
        }
    }
}