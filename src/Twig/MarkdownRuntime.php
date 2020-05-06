<?php

namespace App\Twig;

use League\CommonMark\CommonMarkConverter;

class MarkdownRuntime
{
    private $markdownParser;

    public function __construct(CommonMarkConverter $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function parseMarkdown(?string $content): string
    {
        if (!$content) {
            return '';
        }

        return $this->markdownParser->convertToHtml($content);
    }
}
