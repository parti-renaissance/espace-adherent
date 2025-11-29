<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Interface for simple image aware entities.
 */
interface ImageAwareInterface
{
    public function getImageName(): ?string;

    public function hasImageName(): bool;

    public function getImagePath(): ?string;
}
