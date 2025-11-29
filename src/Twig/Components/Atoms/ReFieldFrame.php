<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReFieldFrame
{
    public ?string $status;
    public string $tag = 'div';
}
