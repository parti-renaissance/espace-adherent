<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReToggleButton extends AbstractComponentsLogic
{
    public string $status = '';
    public bool $checked = false;
    public string $id;
}
