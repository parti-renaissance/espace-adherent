<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReParagraphStatus
{
    public string $status = 'default';
    public ?string $xSyncStatus;
}
