<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IdeasWorkshopExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_idea', [IdeasWorkshopRuntime::class, 'isIdea']),
            new TwigFunction('is_thread', [IdeasWorkshopRuntime::class, 'isThread']),
            new TwigFunction('is_thread_comment', [IdeasWorkshopRuntime::class, 'isThreadComment']),
        ];
    }
}
