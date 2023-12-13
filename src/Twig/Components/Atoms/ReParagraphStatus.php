<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReParagraphStatus
{
    public string $status = 'default';
    public ?string $icon = null;
    public ?string $xSyncStatus;
    public bool $slim = false;
}
