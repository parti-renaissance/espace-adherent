<?php

namespace App\Entity;

interface EntityContentInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): void;

    public function getSlug(): ?string;

    public function setSlug(?string $slug): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getTwitterDescription(): ?string;

    public function setTwitterDescription(?string $twitterDescription): void;

    public function getContent(): ?string;

    public function setContent(?string $content): void;

    public function getAmpContent(): ?string;

    public function setAmpContent(?string $ampContent): void;
}
