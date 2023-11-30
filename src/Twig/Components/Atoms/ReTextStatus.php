<?php

/** @see templates/components/atoms/ReTextStatus.php */

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReTextStatus
{
    public string $status = 'default';
    public ?string $xSyncStatus;
    public string $value = '';
}
