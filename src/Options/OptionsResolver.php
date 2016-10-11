<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Tequila\MongoDB\Exception\InvalidArgumentException;

class OptionsResolver extends \Symfony\Component\OptionsResolver\OptionsResolver
{
    public function resolve(array $options = array())
    {
        try {
            return parent::resolve($options);
        } catch (OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}