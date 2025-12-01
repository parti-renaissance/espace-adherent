<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityDesignationTrait
{
    /**
     * @var Designation
     */
    #[Assert\NotBlank(groups: ['Admin'])]
    #[Assert\Valid(groups: ['Admin'])]
    #[Groups(['committee_election:read'])]
    #[ORM\ManyToOne(targetEntity: Designation::class, cascade: ['persist'])]
    protected $designation;

    public function getDesignation(): ?Designation
    {
        return $this->designation;
    }

    public function setDesignation(Designation $designation): void
    {
        $this->designation = $designation;
    }

    public function getCandidacyPeriodStartDate(): ?\DateTime
    {
        return $this->designation->getCandidacyStartDate();
    }

    public function getCandidacyPeriodEndDate(): ?\DateTime
    {
        return $this->designation->getCandidacyEndDate();
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->designation->getVoteStartDate();
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->designation->getVoteEndDate();
    }

    public function isOngoing(): bool
    {
        return $this->designation && $this->designation->isOngoing();
    }

    public function isCandidacyPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getCandidacyPeriodStartDate() <= $now
            && (
                null === $this->getCandidacyPeriodEndDate()
                || $now < $this->getCandidacyPeriodEndDate()
            );
    }

    public function isCandidacyPeriodStarted(): bool
    {
        $now = new \DateTime();

        return $this->designation && $this->getCandidacyPeriodStartDate() && $this->getCandidacyPeriodStartDate() <= $now;
    }

    public function isVotePeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getVoteStartDate()
            && $this->getVoteStartDate() <= $now
            && (
                null === $this->getVoteEndDate()
                || $now < $this->getVoteEndDate()
            );
    }

    public function isVotePeriodStarted(): bool
    {
        return $this->designation && $this->designation->isVotePeriodStarted();
    }

    public function isBinomeDesignation(): bool
    {
        return $this->designation->isBinomeDesignation();
    }

    public function isResultPeriodActive(): bool
    {
        if (!$voteEndDate = $this->getVoteEndDate()) {
            return false;
        }

        $now = new \DateTime();

        return $this->designation
            && $this->getVoteEndDate() <= $now
            && $now < (clone $voteEndDate)->modify(
                \sprintf('+%d days', $this->designation->getResultDisplayDelay())
            );
    }

    public function isResultPeriodStarted(): bool
    {
        $now = new \DateTime();

        return $this->designation && $this->getVoteEndDate() && $this->getVoteEndDate() <= $now;
    }

    public function getAdditionalRoundDuration(): int
    {
        return $this->designation->getAdditionalRoundDuration();
    }

    public function isLockPeriodActive(): bool
    {
        if (!$candidateEndDate = $this->getCandidacyPeriodEndDate()) {
            return false;
        }

        $now = new \DateTime();
        $candidateEndDate = clone $candidateEndDate;

        return $candidateEndDate->modify(\sprintf('-%d days', $this->designation->getLockPeriodThreshold())) < $now
            && ($this->isCandidacyPeriodActive() || $now < $this->getRealVoteEndDate());
    }

    public function getRealVoteEndDate(): \DateTime
    {
        return $this->getVoteEndDate();
    }

    #[Groups(['committee:read', 'committee_election:read'])]
    public function getStatus(): string
    {
        if (!$this->designation) {
            return DesignationStatusEnum::NOT_STARTED;
        }

        if ($this->designation->isCanceled()) {
            return DesignationStatusEnum::CANCELED;
        }

        if ($this->designation->isCandidacyPeriodEnabled() && !$this->isCandidacyPeriodStarted()) {
            return DesignationStatusEnum::NOT_STARTED;
        }

        $now = new \DateTime();
        if ($this->designation->getElectionCreationDate() && $this->designation->getElectionCreationDate() > $now) {
            return DesignationStatusEnum::NOT_STARTED;
        }

        if (!$this->isVotePeriodStarted()) {
            if ($this->getVoteStartDate()) {
                return DesignationStatusEnum::SCHEDULED;
            }

            return DesignationStatusEnum::OPENED;
        }

        if ($this->isVotePeriodActive()) {
            return DesignationStatusEnum::IN_PROGRESS;
        }

        return DesignationStatusEnum::CLOSED;
    }

    public function getDesignationType(): string
    {
        return $this->designation->getType();
    }
}
