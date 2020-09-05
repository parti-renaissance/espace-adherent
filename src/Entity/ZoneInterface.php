<?php

namespace App\Entity;

interface ZoneInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(string $code): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    /**
     * @return self[]
     */
    public function getParents(): array;
}
