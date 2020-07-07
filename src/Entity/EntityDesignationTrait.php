<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\ORM\Mapping as ORM;

trait EntityDesignationTrait
{
    /**
     * @var Designation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\Designation", fetch="EAGER")
     */
    private $designation;

    public function getDesignation(): Designation
    {
        return $this->designation;
    }

    public function getCandidacyPeriodStartDate(): \DateTime
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

    public function isActive(): bool
    {
        return $this->designation && $this->designation->isActive();
    }

    public function isCandidacyPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getCandidacyPeriodStartDate() <= $now
            && (
                null === $this->getCandidacyPeriodEndDate()
                || $now < $this->getCandidacyPeriodEndDate()
            )
        ;
    }

    public function isVotePeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getVoteStartDate() <= $now
            && (
                null === $this->getVoteEndDate()
                || $now < $this->getVoteEndDate()
            )
        ;
    }

    public function isVotePeriodStarted(): bool
    {
        $now = new \DateTime();

        return $this->designation && $this->getVoteStartDate() <= $now;
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
                sprintf('+%d days', $this->designation->getResultDisplayDelay())
            )
        ;
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

        return $candidateEndDate->modify(sprintf('-%d days', $this->designation->getLockPeriodThreshold())) < $now
            && ($this->isCandidacyPeriodActive() || $now < $this->getRealVoteEndDate());
    }

    public function getRealVoteEndDate(): \DateTime
    {
        return $this->getVoteEndDate();
    }
}
