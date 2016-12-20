<?php

namespace Tequila\MongoDB;

use MongoDB\Driver\ReadConcern;
use Tequila\MongoDB\Exception\InvalidArgumentException;

class Query implements QueryInterface
{
    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ReadConcern
     */
    private $readConcern;

    /**
     * @param array $filter
     * @param array $options
     */
    public function __construct(array $filter, array $options = [])
    {
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        if (isset($this->options['collation']) && !$server->supportsCollation()) {
            throw new InvalidArgumentException(
                'Option "collation" is not supported by the server.'
            );
        }

        if (isset($this->options['readConcern'])) {
            if (!$server->supportsReadConcern()) {
                throw new InvalidArgumentException(
                    'Option "readConcern" is not supported by the server'
                );
            }
        } elseif ($this->readConcern && $server->supportsReadConcern()) {
            $this->options['readConcern'] = $this->readConcern;
        }

        return $this->options;
    }

    /**
     * @param ReadConcern $readConcern
     */
    public function setDefaultReadConcern(ReadConcern $readConcern)
    {
        $this->readConcern = $readConcern;
    }
}