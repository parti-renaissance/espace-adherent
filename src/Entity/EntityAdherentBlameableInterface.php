<?php

namespace App\Entity;

interface EntityAdherentBlameableInterface
{
    public function getCreatedByAdherent(): ?Adherent;

    public function setCreatedByAdherent(?Adherent $createdByAdherent): void;

    public function getUpdatedByAdherent(): ?Adherent;

    public function setUpdatedByAdherent(?Adherent $updatedByAdherent): void;
}
