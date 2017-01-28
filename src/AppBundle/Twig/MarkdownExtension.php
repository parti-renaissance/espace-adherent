<?php

namespace AppBundle\Twig;

use League\CommonMark\CommonMarkConverter;

class MarkdownExtension extends \Twig_Extension
{
    private $markdownParser;

    public function __construct(CommonMarkConverter $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('markdown', [$this, 'parseMarkdown'], ['is_safe' => ['html']]),
        );
    }

    public function parseMarkdown(string $content)
    {
        return $this->markdownParser->convertToHtml($content);
    }
}
