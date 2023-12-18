<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReButton
{
    /**
     * @var string 'blue'|'green'|'yellow'
     */
    public ?string $color = null;
    public ?string $stroke = null;
    public string $tag = 'button';
    public ?string $value = null;
    public ?string $icon;
    public ?string $xSyncLoading;

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

    public function getDefaultClass(): string
    {
        $class = 're-button';

        if ($this->stroke) {
            $class .= ' re-button--stroke';
            if ('black' === $this->color) {
                $class .= ' re-button--stroke--black';
            }
        } else {
            $class .= ' re-button--'.($this->color ?? 'blue');
        }

        return $class;
    }
}
