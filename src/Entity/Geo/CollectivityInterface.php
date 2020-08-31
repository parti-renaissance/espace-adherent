<?php

namespace App\Entity\Geo;

interface CollectivityInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(string $code): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    public function isActive(): bool;

    public function activate(bool $active = true): void;

    /**
     * @return CollectivityInterface[]
     */
    public function getParents(): array;
}
