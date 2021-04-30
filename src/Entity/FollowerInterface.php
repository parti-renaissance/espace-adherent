<?php

namespace App\Entity;

interface FollowerInterface
{
    public function getFollowed(): FollowedInterface;

    public function getAdherent(): ?Adherent;

    public function getEmailAddress(): ?string;

    public function isAdherent(): bool;
}
