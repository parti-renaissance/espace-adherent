<?php

namespace App\Entity;

interface AuthorInstanceInterface extends AuthorInterface
{
    public function getAuthorRole(): ?string;

    public function setAuthorRole(?string $authorRole): void;

    public function getAuthorInstance(): ?string;

    public function setAuthorInstance(?string $authorInstance): void;

    public function getAuthorZone(): ?string;

    public function setAuthorZone(?string $authorZone): void;
}
