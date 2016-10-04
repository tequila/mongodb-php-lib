<?php

namespace Tequila\MongoDB\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface OptionsInterface
{
    public static function configureOptions(OptionsResolver $resolver);
}