<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Base64Extension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('base64_decode', 'base64_decode', ['is_safe' => ['html']]),
            new TwigFilter('base64_encode', 'base64_encode', ['is_safe' => ['html']]),
        ];
    }
}
