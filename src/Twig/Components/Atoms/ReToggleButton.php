<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReToggleButton
{
    public string $status = '';
    public bool $checked = false;
}
