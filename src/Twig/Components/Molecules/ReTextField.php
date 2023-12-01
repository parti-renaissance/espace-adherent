<?php

/** @see templates/components/Molecules/ReTextField.html.twig */

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextField
{
    public ?string $status = 'default';
    public ?string $message;
    public ?string $validate;
}
