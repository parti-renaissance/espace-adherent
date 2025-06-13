<?php

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReCheckboxField
{
    use StatusAwareTrait;

    public ?string $iconToolTip = null;
    public ?string $icon = null;
    public bool $checked = false;
    public string $widgetSide = 'left';
}
