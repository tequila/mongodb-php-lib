<?php

namespace Tequilla\MongoDB\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableInterface
{
    public static function configureOptions(OptionsResolver $resolver);
}