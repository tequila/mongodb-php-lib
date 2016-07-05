<?php

namespace Tequilla\MongoDB\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurableClassInterface
{
    public static function configureOptions(OptionsResolver $resolver);
}