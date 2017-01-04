<?php

namespace Tequila\MongoDB\OptionsResolver\Command;

use Tequila\MongoDB\Exception\UnsupportedException;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Server;

class CompatibilityResolver
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    private static $wireVersionsForOptions = [
        'bypassDocumentValidation' => 4,
        'collation' => 5,
        'readConcern' => 4,
    ];

    /**
     * @param OptionsResolver $optionsResolver
     */
    public function __construct(OptionsResolver $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolveCompatibilities(Server $server, array $options)
    {
        // If resolved readConcern is one that inherited from Client, Database or Collection,
        // and this option is not supported by the server - silently remove this option,
        // since exception should only be thrown only if readConcern has been specified explicitly
        // for this operation
        if (
            $this->optionsResolver->isDefined('readConcern')
            && isset($options['readConcern'])
            && $this->optionsResolver->hasDefault('readConcern')
            // if option is inherited
            && $options['readConcern'] === $this->optionsResolver->getDefault('readConcern')
            && !$server->supportsWireVersion(self::$wireVersionsForOptions['readConcern'])
        ) {
            unset($options['readConcern']);
        }

        // If writeConcern specified, but not supported by the server that executes this operation,
        // this option should be silently omitted due to MongoDB Driver Specifications
        if ($this->optionsResolver->isDefined('writeConcern') && isset($options['writeConcern'])) {
            $wireVersionForWriteConcern = $this->optionsResolver instanceof FindAndModifyResolver ? 4 : 5;
            if (!$server->supportsWireVersion($wireVersionForWriteConcern)) {
                unset($options['writeConcern']);
            }
        }

        // Check if option defined and set, and if server does not support it - throw exception
        foreach (['bypassDocumentValidation', 'collation', 'readConcern',] as $option) {
            if (
                $this->optionsResolver->isDefined($option)
                && isset($options[$option])
                && !$server->supportsWireVersion(self::$wireVersionsForOptions[$option])
            ) {
                throw new UnsupportedException(
                    sprintf('Option "%s" is not supported by the server.', $option)
                );
            }
        }
    }
}