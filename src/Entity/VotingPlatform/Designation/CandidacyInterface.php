<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\Adherent;
use App\Entity\ImageOwnerInterface;
use Ramsey\Uuid\UuidInterface;

interface CandidacyInterface extends ImageOwnerInterface
{
    public const TYPE_TERRITORIAL_COUNCIL = 'territorial_council';
    public const TYPE_NATIONAL_COUNCIL = 'national_council';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';

    public function getId(): ?int;

    public function getUuid(): UuidInterface;

    public function getGender(): string;

    public function setGender(string $gender): void;

    public function getCivility(): string;

    public function isFemale(): bool;

    public function getBiography(): ?string;

    public function setBiography(?string $biography): void;

    public function getType(): string;

    public function getAdherent(): Adherent;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getStatus(): string;

    public function isConfirmed(): bool;

    public function getQuality(): ?string;

    public function getCreatedAt(): \DateTimeInterface;

    public function getUpdatedAt(): \DateTimeInterface;

    public function getElection(): ElectionEntityInterface;

    public function setCandidaciesGroup(?BaseCandidaciesGroup $candidaciesGroup): void;

    public function getCandidaciesGroup(): ?BaseCandidaciesGroup;

    public function hasOtherCandidacies(): bool;

    /** @return CandidacyInvitationInterface[] */
    public function getInvitations(): array;

    public function candidateWith(CandidacyInterface $candidacy): void;

    public function syncWithOtherCandidacies(): void;

    /** @return CandidacyInterface[] */
    public function getOtherCandidacies(): array;
}
