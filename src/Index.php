<?php

namespace Tequila\MongoDB;

use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\Indexes\IndexOptions;

class Index
{
    /**
     * @var array
     */
    private $key;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $key
     * @param array $options
     */
    public function __construct(array $key, array $options = [])
    {
        if (empty($key)) {
            throw new InvalidArgumentException('$key document cannot be empty');
        }

        $options = IndexOptions::resolve($options);

        if (empty($options['name'])) {
            $options['name'] = self::generateIndexName($key);
        }

        $this->key = $key;
        $this->options = $options;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return array
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->options['name'];
    }

    /**
     * @param array $key
     * @return string
     */
    public static function generateIndexName(array $key)
    {
        $nameParts = [];
        foreach ($key as $fieldName => $direction) {
            $nameParts[] = $fieldName;
            $nameParts[] = (string)$direction;
        }

        return implode('_', $nameParts);
    }
}