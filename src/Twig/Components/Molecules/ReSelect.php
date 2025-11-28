<?php

declare(strict_types=1);

namespace App\Twig\Components\Molecules;

use App\Twig\Components\StatusAwareTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReSelect
{
    use StatusAwareTrait;

    public array $preferredOptions = [];
    public array $options = [];
    public ?string $label;
    public ?string $id;
    public ?string $blocked = null;
    public bool $disabled = false;
    public array $searchBoxProps = [];
}
