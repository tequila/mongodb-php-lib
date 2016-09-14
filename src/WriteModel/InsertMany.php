<?php

namespace WriteModel;

use MongoDB\Driver\BulkWrite;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\WriteModel\ValidateDocumentTrait;
use Tequilla\MongoDB\WriteModel\WriteModelInterface;

class InsertMany implements WriteModelInterface
{
    use ValidateDocumentTrait;

    /**
     * @var array|\Traversable
     */
    private $documents;

    /**
     * @param array|\Traversable $documents
     */
    public function __construct($documents)
    {
        if (!is_array($documents) && !$documents instanceof \Traversable) {
            throw new InvalidArgumentException(
                '$documents must be an array or a \\Traversable instance'
            );
        }

        $this->documents = $documents;
    }

    public function writeToBulk(BulkWrite $bulk)
    {
        $insertedIds = [];

        foreach ($this->documents as $index => $document) {
            try {
                self::validateDocument($document);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Exception when trying to parse $documents[%d]: %s',
                        $index,
                        $e->getMessage()
                    )
                );
            }

            $insertedIds[] = $bulk->insert($document);
        }

        return $insertedIds;
    }
}