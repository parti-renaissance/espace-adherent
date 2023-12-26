<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReButton
{
    /**
     * @var string 'blue'|'green'|'yellow'
     */
    public ?string $color = 'blue';
    public ?string $stroke = null;
    public ?string $link = null;
    public string $tag = 'button';
    public ?string $icon;
    public ?string $loading = null;

    public function getLoaderType(): string
    {
        if (!$this->color) {
            return 'loading';
        }
        if ($this->stroke) {
            $color = match ($this->color) {
                'blue', => null,
                default => 'black',
            };
        } else {
            $color = match ($this->color) {
                'yellow' => 'brown',
                default => 'white',
            };
        }

        return $color ? 'loading--'.$color : 'loading';
    }
}
