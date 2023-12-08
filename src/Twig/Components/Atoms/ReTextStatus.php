<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextStatus
{
    public string $status = 'default';
    public ?string $xSyncStatus;
    public ?string $xSyncValue;
    public string $value = '';
    public ?string $showIcon;
}
