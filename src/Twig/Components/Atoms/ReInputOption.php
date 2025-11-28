<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReInputOption
{
    public bool $selected = false;
    public ?string $xSyncSelected;
}
