<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReLinkedToggle
{
    use StatusAwareTrait;

    public string $id;
    public string $label;
    public bool $grid = false;
    public bool $disabled = false;
    public string $color = 'blue';
}
