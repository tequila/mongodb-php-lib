<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\Command;
use Tequila\MongoDB\Exception\UnexpectedResultException;

class AggregationCursor extends CommandCursor
{
    public function getIterator()
    {
        $result = current($this->toArray());

        if (false === $this->options['useCursor']) {
            if (!isset($result->result) || !is_array($result->result)) {
                throw new UnexpectedResultException(
                    'aggregate command did not return expected "result" array'
                );
            }

            return new \ArrayIterator($result->result);
        }

        return $result;
    }

    protected function initMongoCursor()
    {
        if (false === $this->options['useCursor']) {
            $this->mongoCursor = $this->server->executeCommand(
                $this->databaseName,
                new Command($this->options)
            );
        }

        parent::initMongoCursor();
    }
}