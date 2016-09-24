<?php

namespace Tequila\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequila\MongoDB\Options\ConfigurableInterface;

class DriverOptions implements ConfigurableInterface
{
    public static function getAll()
    {
        return TypeMapOptions::getAll();
    }

    public static function configureOptions(OptionsResolver $resolver)
    {
        TypeMapOptions::configureOptions($resolver);
    }
}