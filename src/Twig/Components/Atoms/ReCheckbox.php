<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReCheckbox
{
    public string $type = 'checkbox';
    public string $status = 'default';
    public bool $checked = false;
    public string $widgetSide = 'right';
    public ?string $xSyncStatus;
}
