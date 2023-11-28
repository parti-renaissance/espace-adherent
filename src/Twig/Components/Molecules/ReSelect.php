<?php

/** @see templates/components/molecules/ReSelect.html.twig */

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReSelect
{
    public ?string $status = 'default';
    public array $preferredOptions = [];
    public array $options = [];
    public ?string $message;
    public ?string $validate;
}
