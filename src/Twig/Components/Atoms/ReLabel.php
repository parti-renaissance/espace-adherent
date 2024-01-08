<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReLabel
{
    public ?string $id;
    public bool $labelHtml = false;
}
