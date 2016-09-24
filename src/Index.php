<?php

namespace Tequila\MongoDB;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Exception\InvalidArgumentException;
use Tequila\MongoDB\Options\Indexes\IndexOptions;

class Index
{
    /**
     * @var array
     */
    private $keys;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $name;

    /**
     * @param array $keys
     * @param array $options
     */
    public function __construct(array $keys, array $options = [])
    {
        if (empty($keys)) {
            throw new InvalidArgumentException('$keys array cannot be empty');
        }

        $resolver = new OptionsResolver();
        IndexOptions::configureOptions($resolver);

        try {
            $options = $resolver->resolve($options);
        } catch(\Symfony\Component\OptionsResolver\Exception\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }


        if (empty($options['name'])) {
            $options['name'] = self::generateIndexName($options['key']);
        }

        $this->name = $options['name'];
        $this->keys = $keys;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
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
        return $this->name;
    }

    /**
     * @param array $keys
     * @return string
     */
    public static function generateIndexName(array $keys)
    {
        $nameParts = [];
        foreach ($keys as $fieldName => $direction) {
            $nameParts[] = $fieldName;
            $nameParts[] = (string) $direction;
        }

        return implode('_', $nameParts);
    }

    public function __toString()
    {
        return $this->name;
    }
}