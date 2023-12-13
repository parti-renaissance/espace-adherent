<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReCheckbox
{
    public string $type = 'checkbox';
    public string $status = 'default';
    public bool $checked = false;
    public ?string $xSyncStatus;
}
