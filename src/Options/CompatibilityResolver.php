<?php

namespace Tequila\MongoDB\Options;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\ServerInfo;

class CompatibilityResolver
{
    /**
     * @var ServerInfo
     */
    private $serverInfo;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $optionsToCheck;

    /**
     * @param ServerInfo $serverInfo
     * @param array $options
     * @param array $optionsToCheck
     */
    public function __construct(ServerInfo $serverInfo, array $options, array $optionsToCheck)
    {
        $this->serverInfo = $serverInfo;
        $this->options = $options;

        foreach ($optionsToCheck as $optionName) {
            if (!method_exists($this, $optionName)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Unknown option "%s" in $optionsToCheck',
                        $optionName
                    )
                );
            }
        }

        $this->optionsToCheck = $optionsToCheck;
    }

    /**
     * @param ServerInfo $serverInfo
     * @param array $options
     * @param array $optionsToCheck
     * @return static
     */
    public static function getInstance(ServerInfo $serverInfo, array $options, array $optionsToCheck)
    {
        return new static($serverInfo, $options, $optionsToCheck);
    }

    /**
     * @return array
     */
    public function resolve()
    {
        foreach ($this->optionsToCheck as $optionName) {
            if (array_key_exists($optionName, $this->options)) {
                call_user_func([$this, $optionName]);
            }
        }

        return $this->options;
    }

    private function bypassDocumentValidation()
    {
        $wireVersion = 4;

        if (!$this->serverInfo->supportsFeature($wireVersion)) {
            throw new UnsupportedException(
                'Option "bypassDocumentValidation" is not supported by the server'
            );
        }
    }

    private function collation()
    {
        $wireVersion = 5;

        if (!$this->serverInfo->supportsFeature($wireVersion)) {
            throw new UnsupportedException('Option "collation" is not supported by the server');
        }
    }

    private function readConcern()
    {
        $wireVersion = 4;

        if (!$this->serverInfo->supportsFeature($wireVersion)) {
            unset($this->options['readConcern']);
        }
    }

    private function writeConcern()
    {
        $wireVersion = 5;

        if (!$this->serverInfo->supportsFeature($wireVersion)) {
            unset($this->options['writeConcern']);
        }
    }
}