<?php

namespace Tequilla\MongoDB\Util;

class StringUtils
{
    public static function startsWith($string, $substring)
    {
        return 0 === mb_strpos($string, $substring);
    }
}