<?php

namespace AppBundle\Utils;

class HtmlPurifier
{
    public static function purify(?string $string)
    {
        if (!$string) {
            return $string;
        }

        $replacements = [];

        foreach (self::$allowed as $from => $to) {
            $replacements['&lt;'.$from.'&gt;'] = $to;
        }

        $string = preg_replace('/<a\s[^>]*href=(\"??)(http[^\" >]*?)\\1[^>]*>(.*)<\/a>/siU', '{link|$2|$3}', $string);
        $string = preg_replace('/<img\s[^>]*src=(\"??)(http[^\" >]*?)\\1[^>]* \/>/iU', '{img|$2}', $string);

        $string = str_replace(
            array_keys($replacements),
            array_values($replacements),
            htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false)
        );

        $string = preg_replace('/\{link\|([^|]+)\|([^\}]+)\}/', '<a href="$1">$2</a>', $string);
        $string = preg_replace('/\{img\|([^\}]+)\}/', '<img src="$1" style="max-width: 450px; max-height: 450px;" />', $string);

        return $string;
    }

    private static $allowed = [
        'h1' => '<h1>',
        '/h1' => '</h1>',
        'h2' => '<h2>',
        '/h2' => '</h2>',
        'h3' => '<h3>',
        '/h3' => '</h3>',
        'h4' => '<h4>',
        '/h4' => '</h4>',
        'h5' => '<h5>',
        '/h5' => '</h5>',
        'h6' => '<h6>',
        '/h6' => '</h6>',
        'pre' => '<p>',
        '/pre' => '</p>',
        'address' => '<p>',
        '/address' => '</p>',
        'div' => '<p>',
        '/div' => '</p>',
        'p' => '<p>',
        'center' => '<center>',
        '/center' => '</center>',
        'p style=&quot;text-align:center&quot;' => '<p style="text-align: center;">',
        'p style=&quot;text-align:right&quot;' => '<p style="text-align: right;">',
        '/p' => '</p>',
        'strong' => '<strong>',
        '/strong' => '</strong>',
        'em' => '<em>',
        '/em' => '</em>',
        'u' => '<u>',
        '/u' => '</u>',
        's' => '<s>',
        '/s' => '</s>',
        'ol' => '<ol>',
        '/ol' => '</ol>',
        'ul' => '<ul>',
        '/ul' => '</ul>',
        'li' => '<li>',
        '/li' => '</li>',
        'br' => '<br>',
        'br/' => '<br/>',
        'br /' => '<br />',
    ];
}
