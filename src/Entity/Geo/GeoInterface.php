<?php

namespace App\Entity\Geo;

interface GeoInterface
{
    public function getId(): ?int;

    public function getCode(): string;

    public function getName(): string;

    public function setName(string $name): void;

    public function isActive(): bool;

    public function activate(bool $active = true): void;
}
