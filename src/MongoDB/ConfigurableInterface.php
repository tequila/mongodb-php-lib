<?php

namespace Tequilla\MongoDB;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface
{
    public static function configureOptions(OptionsResolver $resolver);
}