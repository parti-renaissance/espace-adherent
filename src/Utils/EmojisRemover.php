<?php

namespace App\Utils;

class EmojisRemover
{
    public static function remove($text)
    {
        if (!$text || !\is_string($text)) {
            return null;
        }

        return mb_convert_encoding(preg_replace('/'.implode('|', self::$matchers).'/u', '', $text), 'UTF-8', 'UTF-8');
    }

    private static $matchers = [
        '([0-9|#][\x{20E3}])',
        '[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?',
        '[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?',
        '[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?',
        '[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?',
        '[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?',
        '[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?',
        '[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?',
        '[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?',
        '[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?',
        '🤐',
        '🤑',
        '🤒',
        '🤓',
        '🤔',
        '🤕',
        '🤖',
        '🤗',
        '🤘',
        '🦀',
        '🦁',
        '🦂',
        '🦃',
        '🦄',
        '🧀',
    ];
}
