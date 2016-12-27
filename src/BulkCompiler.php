<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\OptionsResolver\BulkWrite\BulkWriteResolver;
use Tequila\MongoDB\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Write\Model\WriteModelInterface;

class BulkCompiler implements BulkCompilerInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var WriteModelInterface[]
     */
    private $writeModels = [];

    /**
     * @param array $options - options that will be passed to @see BulkWrite::__construct()
     */
    public function __construct(array $options = [])
    {
        $this->options = OptionsResolver::get(BulkWriteResolver::class)->resolve($options);
    }

    /**
     * Adds a write model to bulk
     *
     * @param WriteModelInterface|WriteModelInterface[] $writeModels
     */
    public function add($writeModels)
    {
        $writeModels = is_array($writeModels) ? array_values($writeModels) : [$writeModels];

        if (empty($writeModels)) {
            throw new InvalidArgumentException('$writeModels cannot be empty.');
        }

        foreach ($writeModels as $i => $model) {
            if (!$model instanceof WriteModelInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Each write model must be an instance of "%s", "%s" given in $writeModels[%d].',
                        WriteModelInterface::class,
                        getType($model),
                        $i
                    )
                );
            }
        }

        $this->writeModels = array_merge($this->writeModels, $writeModels);
    }

    /**
     * @inheritdoc
     */
    public function compile(BulkWrite $bulkWrite, Server $server)
    {
        if (0 === count($this->writeModels)) {
            throw new LogicException('No write operations were added.');
        }

        foreach ($this->writeModels as $position => $writeModel) {
            try {
                $writeModel->writeToBulk($bulkWrite, $server);
            } catch(InvalidArgumentException $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Exception during parsing $writeModels[%d]: %s',
                        $position,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getOptions(Server $server)
    {
        if (isset($this->options['bypassDocumentValidation']) && !$server->supportsDocumentValidation()) {
            throw new InvalidArgumentException(
                'Option "bypassDocumentValidation" is not supported by the server.'
            );
        }

        return $this->options;
    }
}