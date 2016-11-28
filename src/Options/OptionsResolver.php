<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\OptionsResolver as BaseResolver;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverException;
use Tequila\MongoDB\Exception\InvalidArgumentException;

abstract class OptionsResolver extends BaseResolver
{
    public function resolve(array $options = array())
    {
        try {
            return parent::resolve($options);
        } catch (OptionsResolverException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function configureOptions()
    {
    }
}