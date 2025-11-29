<?php

declare(strict_types=1);

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextField
{
    use StatusAwareTrait;

    public ?string $label = null;
    public ?string $id = null;
    public ?string $icon = null;
    public bool $center = false;
}
