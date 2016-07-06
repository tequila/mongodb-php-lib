<?php

namespace Tequilla\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableInterface;

/**
 * Class DriverOptions
 * @package Tequilla\MongoDB\Options
 */
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