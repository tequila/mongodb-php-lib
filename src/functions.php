<?php

namespace Tequilla\MongoDB;

/**
 * This function is intended for us in OptionsResolver,
 * it gives the ability to call OptionsResolver::setAllowedTypes($options, 'list');
 *
 * @param $value
 * @return bool
 */
function is_list($value) {
    if (!is_array($value)) {
        return false;
    }

    return array_keys($value) === range(0, count($value) - 1);
}