<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\ImageOwnerInterface;
use Ramsey\Uuid\UuidInterface;

interface CandidacyInterface extends ImageOwnerInterface
{
    public function getId(): ?int;

    public function getUuid(): UuidInterface;

    public function getGender(): string;

    public function setGender(string $gender): void;

    public function getCivility(): string;

    public function isMale(): bool;

    public function isFemale(): bool;

    public function getBiography(): ?string;

    public function setBiography(?string $biography): void;

    public function isRemoveImage(): bool;

    public function setRemoveImage(bool $value): void;
}
