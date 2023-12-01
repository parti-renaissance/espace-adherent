<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReButton
{
    /**
     * @var string 'blue'|'green'|'yellow'
     */
    public string $color = 'blue';
    public string $tag = 'button';
    public string $value = '';
    public ?string $icon;
    public ?string $xSyncLoading;
}
