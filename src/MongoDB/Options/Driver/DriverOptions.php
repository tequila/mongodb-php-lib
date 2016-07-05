<?php

namespace Tequilla\MongoDB\Options\Driver;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tequilla\MongoDB\Options\ConfigurableClassInterface;

/**
 * Class DriverOptions
 * @package Tequilla\MongoDB\Options
 */
class DriverOptions implements ConfigurableClassInterface
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