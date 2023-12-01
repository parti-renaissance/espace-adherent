<?php

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReCheckboxField
{
    use StatusAwareTrait;

    public ?string $iconTooltip;
    public ?string $icon;
}
