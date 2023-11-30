<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReIcon
{
    /**
     * @var 'success'|'error'|'warning'|'info'|'valid'|'arrow-direction'|'chevron'|'star'
     */
    public string $type = 'success';
    public ?string $xSyncType;

    public function getFileName(): string
    {
        $type = $this->type;
        if (str_starts_with($type, 'arrow')) {
            $type = 'arrow';
        }

        if (str_starts_with($type, 'star')) {
            $type = 'star';
        }

        return $type;
    }
}
