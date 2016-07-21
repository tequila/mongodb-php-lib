<?php

namespace Tequilla\MongoDB;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Exception\InvalidArgumentException;
use Tequilla\MongoDB\Options\Indexes\IndexOptions;

/**
 * Class Index
 * @package Tequilla\MongoDB
 */
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
     * Index constructor.
     * @param array $keys
     * @param array $options
     */
    public function __construct(array $keys, array $options = [])
    {
        if (empty($keys)) {
            throw new InvalidArgumentException('$keys array cannot be empty');
        }

        foreach ($keys as $fieldName => $value) {
            ensureValidDocumentFieldName($fieldName);
        }

        $resolver = new OptionsResolver();
        IndexOptions::configureOptions($resolver);
        $options = $resolver->resolve($options);

        if (empty($options['name'])) {
            $nameParts = [];
            foreach ($keys as $fieldName => $direction) {
                $nameParts += [$fieldName, $direction];
            }

            $options['name'] = implode('_', $nameParts);
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

    public function __toString()
    {
        return $this->name;
    }
}