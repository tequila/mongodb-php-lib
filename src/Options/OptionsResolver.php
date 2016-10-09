<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Tequila\MongoDB\Exception\InvalidArgumentException;

class OptionsResolver extends \Symfony\Component\OptionsResolver\OptionsResolver
{
    public function resolve(array $options = array())
    {
        try {
            $options = parent::resolve($options);

            return array_filter($options, function($optionValue) {
                return null !== $optionValue; // ability to delete option by setting it to null
            });
        } catch (OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}