<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextStatus
{
    public string $status = 'default';
    public ?string $xSyncStatus = null;
    public ?string $xSyncValue = null;
    public string $value = '';
    public ?string $showIcon;
}
