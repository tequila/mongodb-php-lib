<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Exception\LogicException;
use Tequila\MongoDB\Util\TypeUtil;
use Tequila\MongoDB\Write\Model\WriteModelInterface;

class BulkWriteBuilder
{
    /**
     * @var \Tequila\MongoDB\Write\Model\WriteModelInterface[]
     */
    private $writeModels;

    /**
     * Adds a write model to bulk
     *
     * @param WriteModelInterface $writeModel
     */
    public function add(WriteModelInterface $writeModel)
    {
        $this->writeModels[] = $writeModel;
    }

    /**
     * Adds an array of write models to the bulk
     *
     * @param WriteModelInterface[] $writeModels
     */
    public function addMany(array $writeModels)
    {
        $writeModels = array_values($writeModels);
        if (empty($writeModels)) {
            throw new InvalidArgumentException('$writeModels cannot be empty');
        }

        foreach ($writeModels as $i => $writeModel) {
            if (!$writeModel instanceof WriteModelInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Each write model must be an instance of %s, %s given in $writeModels[%d]',
                        WriteModelInterface::class,
                        TypeUtil::getType($writeModel),
                        $i
                    )
                );
            }

            $this->add($writeModel);
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->writeModels);
    }

    /**
     * @param array $options
     * @return BulkWrite
     */
    public function getBulk(array $options = [])
    {
        if (0 === $this->count()) {
            throw new LogicException('No operations were added to the bulk.');
        }

        $bulk = new BulkWrite($options);

        foreach ($this->writeModels as $writeModel) {
            $writeModel->writeToBulk($bulk);
        }

        $this->writeModels = [];

        return $bulk;
    }
}