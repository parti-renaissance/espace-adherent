<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReFieldFrame
{
    public ?string $status;
    public string $tag = 'div';
}
