<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\Adherent;
use App\Entity\ImageOwnerInterface;
use Ramsey\Uuid\UuidInterface;

interface CandidacyInterface extends ImageOwnerInterface
{
    public const TYPE_COMMITTEE = 'committee';
    public const TYPE_TERRITORIAL_COUNCIL = 'territorial_council';

    public function getId(): ?int;

    public function getUuid(): UuidInterface;

    public function getGender(): string;

    public function setGender(string $gender): void;

    public function getCivility(): string;

    public function isFemale(): bool;

    public function getBiography(): ?string;

    public function setBiography(?string $biography): void;

    public function isRemoveImage(): bool;

    public function setRemoveImage(bool $value): void;

    public function getType(): string;

    public function getAdherent(): Adherent;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getStatus(): string;

    public function getQuality(): ?string;

    public function getCreatedAt(): \DateTimeInterface;

    public function getUpdatedAt(): \DateTimeInterface;

    public function getElection(): ElectionEntityInterface;
}
