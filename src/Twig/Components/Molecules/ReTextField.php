<?php

/** @see templates/components/Molecules/ReTextField.html.twig */

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextField
{
    use StatusAwareTrait;
}
