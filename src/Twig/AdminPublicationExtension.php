<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminPublicationExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_publication_stats', [AdminPublicationRuntime::class, 'getPublicationStats']),
        ];
    }
}
