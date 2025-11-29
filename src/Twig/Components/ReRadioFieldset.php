<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReRadioFieldset
{
    use StatusAwareTrait;

    public array|FormErrorIterator $errors = [];
    public ?string $label = null;
    public ?string $iconToolTip = null;
}
