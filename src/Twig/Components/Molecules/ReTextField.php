<?php

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextField
{
    use StatusAwareTrait;

    public ?string $label;
    public ?string $id;
}
