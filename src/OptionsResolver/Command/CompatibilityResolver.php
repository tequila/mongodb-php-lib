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
     * {@inheritdoc}
     */
    public function resolveCompatibilities(Server $server, array $options)
    {
        // if "readConcern" option is inherited from Collection, Database or Client
        // and is not supported by the server executing this command - silently omit the option
        if (
            $this->optionsResolver->isDefined('readConcern')
            && isset($options['readConcern'])
            && $this->optionsResolver->hasDefault('readConcern')
            // if option is inherited from Collection, Database or Client and not supported
            && $options['readConcern'] === $this->optionsResolver->getDefault('readConcern')
            && !$server->supportsWireVersion(self::$wireVersionsForOptions['readConcern'])
        ) {
            unset($options['readConcern']);
        }

        // if "writeConcern" option is inherited from Collection, Database or Client
        // and is not supported by the server executing this command - silently omit the option
        if ($this->optionsResolver->isDefined('writeConcern') && isset($options['writeConcern'])) {
            $wireVersionForWriteConcern = $this->optionsResolver instanceof FindAndModifyResolver ? 4 : 5;

            if (!$server->supportsWireVersion($wireVersionForWriteConcern)) {
                // if option is inherited - from Collection, Client or Database - omit the option
                // else - throw an exception
                if (
                    $this->optionsResolver->hasDefault('writeConcern')
                    && $options['writeConcern'] === $this->optionsResolver->getDefault('writeConcern')
                ) {
                    unset($options['writeConcern']);
                } else {
                    throw new UnsupportedException(
                        'Option "writeConcern" is not supported by the server.'
                    );
                }
            }
        }

        // Check if option defined and set, and if server does not support it - throw exception
        foreach (['bypassDocumentValidation', 'collation', 'readConcern'] as $option) {
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
