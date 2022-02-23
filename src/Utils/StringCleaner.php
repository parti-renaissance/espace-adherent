<?php

namespace App\Utils;

class StringCleaner
{
    public static function htmlspecialchars(string $value, int $flags = \ENT_NOQUOTES): string
    {
        return htmlspecialchars($value, $flags, 'UTF-8', false);
    }

    public static function removeMarkdown(string $text): string
    {
        // remove images and links
        $text = preg_replace('/\[(.*?)\]\s*\(((?:http:\/\/|https:\/\/)(?:.+))\)/', '', $text);
        // remove bold and italic
        $text = preg_replace('/([\*_]{1,3})(\S.*?\S{0,1})\1/', '$2', $text);
        // remove line break, * and #
        $text = str_replace(["\r\n", "\r", "\n", '*', '#', '  '], ' ', $text);
        $text = trim($text);

        return $text;
    }
}
