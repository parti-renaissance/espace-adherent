<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ReadMoreExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('read_more', [__CLASS__, 'addReadMoreLink'], ['is_safe' => ['html']]),
        ];
    }

    public static function addReadMoreLink(
        $text,
        $length = 256,
        $salt = null,
        $readMoreLabelText = 'Show more',
        $readLessLabelText = 'Show less'
    ) {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $text = strip_tags($text);

        $whiteSpaceIndex = mb_strpos($text, ' ', $length);

        $textId = md5($text.$salt);

        $htmlParts = [
            sprintf('<input type="checkbox" class="read-more-state" id="read-more-post-%s" />', $textId),
            '<p class="read-more-wrap">',
            mb_substr($text, 0, $whiteSpaceIndex),
            '<span class="read-more-ellipsis">…</span>',
            '<span class="read-more-target">',
            mb_substr($text, $whiteSpaceIndex),
            '</span>',
            '</p>',
            sprintf(
                '<label for="read-more-post-%s" class="read-more-trigger" data-show-more="%s" data-show-less="%s"></label>',
                $textId,
                $readMoreLabelText,
                $readLessLabelText
            ),
        ];

        return implode($htmlParts);
    }
}
