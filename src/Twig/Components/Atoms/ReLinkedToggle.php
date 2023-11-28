<?php

namespace App\Twig\Components\Atoms;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReLinkedToggle
{
    use StatusAwareTrait;
}
