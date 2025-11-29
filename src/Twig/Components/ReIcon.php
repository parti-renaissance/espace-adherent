<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\AbstractComponentsLogic;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReIcon extends AbstractComponentsLogic
{
    public string $name;

    public function getIconId(): string
    {
        $type = $this->props['name']['twig'];
        if (str_starts_with($type, 'arrow')) {
            $type = 'arrow';
        }

        if (str_starts_with($type, 'star')) {
            $type = 'star';
        }

        return $type;
    }

    public function getClassName(): string
    {
        $type = $this->props['name']['twig'];
        $class = str_starts_with($type, 'loading') ? 'loading' : $type;

        return "re-icon re-icon-$class";
    }
}
