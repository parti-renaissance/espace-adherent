<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('markdown', [MarkdownRuntime::class, 'parseMarkdown'], ['is_safe' => ['html']]),
        ];
    }
}
