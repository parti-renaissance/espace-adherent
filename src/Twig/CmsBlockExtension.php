<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CmsBlockExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cms_block', [CmsBlockRuntime::class, 'getCmsBlockContent'], ['is_safe' => ['html']]),
        ];
    }
}
