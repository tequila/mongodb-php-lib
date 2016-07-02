<?php

namespace Tequilla\MongoDB\Options;

interface OptionsInterface
{
    /**
     * @return string[] array of option names
     */
    public static function getAll();
}