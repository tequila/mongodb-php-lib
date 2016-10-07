<?php

namespace Tequila\MongoDB\Command\Result;

class CollectionInfo
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $collectionInfo
     */
    public function __construct(array $collectionInfo)
    {
        $this->name = (string)$collectionInfo['name'];
        $this->options = (array)$collectionInfo['options'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}