<?php

namespace AppBundle\Entity;

interface CoordinatorAreaInterface
{
    public const PRE_APPROVED = 'PRE_APPROVED';
    public const PRE_REFUSED = 'PRE_REFUSED';

    public function getCoordinatorComment(): ?string;

    public function setCoordinatorComment(?string $comment): void;

    public function getCreator(): ?Adherent;

    public function setCreator(?Adherent $adherent): void;

    public function preRefused(): void;

    public function preApproved(): void;

    public function isPreRefused(): bool;

    public function isPreApproved(): bool;
}
