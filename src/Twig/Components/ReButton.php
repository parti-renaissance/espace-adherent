<?php

namespace App\Twig\Components;

use App\Twig\AbstractTailwindMerge;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReButton extends AbstractTailwindMerge
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

    private function getVariantColorClasses(): string
    {
        return match ($this->color) {
            'blue' => <<<TW
                bg-ui_blue-50 text-ui_blue-5
                hover:enabled:bg-ui_blue-60
                active:enabled:bg-ui_blue-70
                disabled:bg_ui_blue-30 disabled:text-ui_blue-5
                backdrop-blur-lg
                TW,
            'green' => <<<TW
                bg-ui_green-50 text-ui_green-5
                hover:enabled:bg-ui_green-60
                active:enabled:bg-ui_green-70
                disabled:bg_ui_green-30 disabled:text-ui_green-5
                TW,
            'yellow' => <<<TW
                bg-ui_yellow-50 text-ui_yellow-100
                hover:enabled:bg-ui_yellow-45
                active:enabled:bg-ui_yellow-70
                disabled:bg_ui_yellow-30 disabled:text-ui_yellow-80
                TW,
            default => throw new \LogicException(sprintf('Unknown button type "%s"', $this->color))
        };
    }

    private function getVariantStrokeClasses(): string
    {
        return match ($this->color) {
            'blue' => <<<TW
                bg-white text-ui_blue-50
                hover:enabled:bg-ui_blue-5
                TW,
            'black' => <<<TW
                bg-white border-ui_gray-30 text-ui_gray-80
                hover:enabled:bg-ui_gray-5
                TW,
            default => throw new \LogicException(sprintf('Unknown button type "%s"', $this->color))
        };
    }

    public function getVariantClasses(): string
    {
        if ($this->link) {
            return <<<TW
                border-0 text-ui_blue-50 bg-transparent focus:outline-none
                TW;
        }

        if ($this->stroke) {
            return "border {$this->getVariantStrokeClasses()}";
        }

        return $this->getVariantColorClasses();
    }

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
