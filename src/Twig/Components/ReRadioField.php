<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReRadioField
{
    use StatusAwareTrait;

    public ?string $iconToolTip = null;
    public ?string $icon = null;
    public bool $checked = false;
}
