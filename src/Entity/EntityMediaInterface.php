<?php

namespace App\Entity;

interface EntityMediaInterface
{
    public function getMedia(): ?Media;

    public function displayMedia(): bool;
}
