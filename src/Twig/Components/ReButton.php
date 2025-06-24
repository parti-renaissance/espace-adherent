<?php

namespace App\Twig\Components;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReButton extends AbstractComponentsLogic
{
    /**
     * @var string 'blue'|'green'|'yellow'|'gray'|'white'|'black'|'red'
     */
    public ?string $color = 'blue';
    public ?string $stroke = null;
    public ?string $link = null;
    public string $tag = 'button';
    public ?string $icon;
    public ?string $loading = null;
    public ?string $disabled = null;
    public ?string $onDisabledClick = null;

    private function getVariantColorClasses(): string
    {
        return match ($this->color) {
            'blue' => <<<TW
                bg-ui_blue-50 text-ui_blue-5
                hover:enabled:bg-ui_blue-60
                active:enabled:bg-ui_blue-70
                disabled:bg-ui_blue-30 disabled:text-ui_blue-5
                backdrop-blur-lg
                TW,
            'green' => <<<TW
                bg-ui_green-50 text-ui_green-5
                hover:enabled:bg-ui_green-60
                active:enabled:bg-ui_green-70
                disabled:bg-ui_green-30 disabled:text-ui_green-5
                TW,
            'yellow' => <<<TW
                bg-ui_yellow-50 text-ui_yellow-100
                hover:enabled:bg-ui_yellow-45
                active:enabled:bg-ui_yellow-70
                disabled:bg-ui_yellow-30 disabled:text-ui_yellow-80
                TW,
            'gray' => <<<TW
                bg-ui_gray-90 text-white
                border border-ui_gray-90
                hover:text-ui_gray-90 hover:bg-white
                active:not(:disabled):bg-ui_gray-70
                disabled:bg-ui_gray-30 disabled:text-white
                TW,
            'black' => <<<TW
                bg-black text-white
                border border-black
                hover:text-black hover:bg-white
                active:not(:disabled):bg-ui_gray-70
                disabled:bg-ui_gray-30 disabled:text-white
                TW,
            'white' => <<<TW
                bg-white text-black
                border border-ui_gray-30
                hover:text-white hover:bg-ui_gray-40
                active:not(:disabled):bg-ui_gray-70
                disabled:bg-ui_gray-30 disabled:text-white
                TW,
            'red' => <<<TW
                bg-ui_red-50 text-white
                border border-ui_red-50
                hover:bg-ui_red-70
                active:not(:disabled):bg-ui_red-90
                disabled:bg-ui_gray-30 disabled:text-white
                TW,
            default => throw new \LogicException(\sprintf('Unknown button type "%s"', $this->color)),
        };
    }

    private function getVariantStrokeClasses(): string
    {
        return match ($this->color) {
            'blue' => <<<TW
                bg-white text-ui_blue-50 border-ui_blue-50
                hover:enabled:bg-ui_blue-5
                TW,
            'black' => <<<TW
                bg-white border-ui_gray-30 text-ui_gray-80
                hover:enabled:bg-ui_gray-5
                TW,
            default => throw new \LogicException(\sprintf('Unknown button type "%s"', $this->color)),
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
