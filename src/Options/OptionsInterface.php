<?php

namespace Tequila\MongoDB\Options;

interface OptionsInterface
{
    public static function configureOptions(OptionsResolver $resolver);
}