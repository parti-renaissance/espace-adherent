<?php

declare(strict_types=1);

namespace App\Entity;

interface EntityMediaInterface
{
    public function getMedia(): ?Media;

    public function displayMedia(): bool;
}
