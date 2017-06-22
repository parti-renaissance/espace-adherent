<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('markdown', [MarkdownRuntime::class, 'parseMarkdown'], ['is_safe' => ['html']]),
        );
    }
}
