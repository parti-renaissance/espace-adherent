<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

class PreheaderExtractor
{
    private const MAX_LENGTH = 90;

    public function extract(?string $htmlContent): string
    {
        if (null === $htmlContent || '' === trim($htmlContent)) {
            return '';
        }

        if (false !== ($headingPos = stripos($htmlContent, '<h1'))) {
            $htmlContent = substr($htmlContent, $headingPos);
        }

        $text = strip_tags($htmlContent);

        $text = html_entity_decode($text, \ENT_QUOTES | \ENT_HTML5);

        $text = preg_replace('/\{{2,3}[^{}]*\}{2,3}/u', '', $text);

        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));

        return rtrim(mb_substr($text, 0, self::MAX_LENGTH));
    }
}
