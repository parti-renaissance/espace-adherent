<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReRadioField
{
    use StatusAwareTrait;

    public ?string $iconToolTip = null;
    public ?string $icon = null;
    public bool $checked = false;
    public string $widgetSide = 'left';
}
