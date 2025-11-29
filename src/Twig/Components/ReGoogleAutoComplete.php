<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReGoogleAutoComplete extends AbstractComponentsLogic
{
    use StatusAwareTrait;

    public ?string $label;
    public ?string $id;
    public array $searchBoxProps = [];
    public string $associatedFieldsPrefix;
}
