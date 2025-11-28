<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TailwindExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('tw_html', [$this, 'generateHtmlClass'], ['is_safe' => ['html']]),
        ];
    }

    public function generateHtmlClass(string $html): string
    {
        return str_replace('<h1>', '<h1 class="font-bold text-3xl">', $html);
    }
}
