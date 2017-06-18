<?php

namespace AppBundle\Entity;

interface EntityMediaInterface
{
    public function getMedia(): ?Media;

    public function displayMedia(): bool;
}
