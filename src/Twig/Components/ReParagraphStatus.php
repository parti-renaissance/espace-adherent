<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReParagraphStatus extends AbstractComponentsLogic
{
    public string $status = 'default';
    public ?string $icon = null;
    public bool $slim = false;
}
