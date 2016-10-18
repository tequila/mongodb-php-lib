<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\Operation\Find;

class Cursor implements CursorInterface
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \MongoDB\Driver\Cursor
     */
    private $mongoCursor;

    /**
     * @param Manager $manager
     * @param string $databaseName
     * @param string $collectionName
     * @param array $filter
     * @param array $options
     */
    public function __construct(Manager $manager, $databaseName, $collectionName, array $filter, array $options)
    {
        $this->manager = $manager;
        $this->databaseName = (string)$databaseName;
        $this->collectionName = (string)$collectionName;
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * @return $this
     */
    public function allowPartialResults()
    {
        $this->options['allowPartialResults'] = true;

        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function batchSize($size)
    {
        $this->options['batchSize'] = (int)$size;

        return $this;
    }

    /**
     * @param array|object $collation
     * @return $this
     */
    public function collation($collation)
    {
        $this->options['collation'] = (object)$collation;

        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function comment($comment)
    {
        $this->options['comment'] = (string)$comment;

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->options['limit'] = (int)$limit;

        return $this;
    }

    /**
     * @param int $maxTimeMS
     * @return $this
     */
    public function maxTimeMS($maxTimeMS)
    {
        $this->options['maxTimeMS'] = (int)$maxTimeMS;

        return $this;
    }

    /**
     * @param array|object $modifiers
     * @return $this
     */
    public function modifiers($modifiers)
    {
        $this->options['modifiers'] = (object)$modifiers;

        return $this;
    }

    /**
     * return $this
     */
    public function noCursorTimeout()
    {
        $this->options['noCursorTimeout'] = true;

        return $this;
    }

    /**
     * @param array|object $projection
     * @return $this
     */
    public function projection($projection)
    {
        $this->options['projection'] = (object)$projection;

        return $this;
    }

    /**
     * @param ReadConcern $readConcern
     * @return $this
     */
    public function setReadConcern(ReadConcern $readConcern)
    {
        $this->options['readConcern'] = $readConcern;

        return $this;
    }

    /**
     * @param ReadPreference $readPreference
     * @return $this
     */
    public function setReadPreference(ReadPreference $readPreference)
    {
        $this->options['readPreference'] = $readPreference;

        return $this;
    }

    /**
     * @param int $skip
     * @return $this
     */
    public function skip($skip)
    {
        $this->options['skip'] = (int)$skip;

        return $this;
    }

    /**
     * @param array|object $sort
     * @return $this
     */
    public function sort($sort)
    {
        $this->options['sort'] = (object)$sort;

        return $this;
    }

    /**
     * @param array $typeMap
     * @return $this
     */
    public function setTypeMap(array $typeMap)
    {
        $this->options['typeMap'] = $typeMap;

        return $this;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        if (null === $this->mongoCursor) {
            $operation = new Find($this->filter, $this->options);
            $this->mongoCursor = $operation->execute($this->manager, $this->databaseName, $this->collectionName);
        }

        foreach ($this->mongoCursor as $document) {
            yield $document;
        }
    }
}