<?php

namespace App\Entity;

interface AuthorInstanceInterface extends AuthorInterface
{
    public function getAuthorScope(): ?string;

    public function setAuthorScope(?string $authorScope);

    public function getAuthorRole(): ?string;

    public function setAuthorRole(?string $authorRole): void;

    public function getAuthorInstance(): ?string;

    public function setAuthorInstance(?string $authorInstance): void;

    public function getAuthorZone(): ?string;

    public function setAuthorZone(?string $authorZone): void;

    public function getAuthorTheme(): ?array;

    public function setAuthorTheme(?array $theme): void;
}
