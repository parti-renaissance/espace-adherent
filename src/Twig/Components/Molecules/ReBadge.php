<?php

namespace App\Twig\Components\Molecules;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReBadge extends AbstractComponentsLogic
{
    public string $status = 'default';
    public ?string $icon = null;
}
