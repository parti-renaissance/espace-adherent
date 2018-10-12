<?php

namespace AppBundle\Utils;

class StringCleaner
{
    public static function htmlspecialchars(string $value, int $flags = \ENT_NOQUOTES): string
    {
        return htmlspecialchars($value, $flags, 'UTF-8', false);
    }
}
