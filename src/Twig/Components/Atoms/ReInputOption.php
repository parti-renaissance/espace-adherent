<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReInputOption
{
    public bool $selected = false;
    public ?string $xSyncSelected;
}
