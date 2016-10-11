<?php

namespace Tequila\MongoDB\Command\Result;

class DatabaseInfo
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $sizeOnDisk;

    /**
     * @var bool
     */
    private $empty;

    /**
     * @param array $dbInfo
     */
    public function __construct(array $dbInfo)
    {
        $this->name = (string)$dbInfo['name'];
        $this->sizeOnDisk = (float)$dbInfo['sizeOnDisk'];
        $this->empty = (bool)$dbInfo['empty'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getSizeOnDisk()
    {
        return $this->sizeOnDisk;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->empty;
    }
}