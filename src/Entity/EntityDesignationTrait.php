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

    public function getCandidacyPeriodEndDate(): \DateTime
    {
        return $this->designation->getCandidacyEndDate();
    }

    public function getVoteStartDate(): \DateTime
    {
        return $this->designation->getVoteStartDate();
    }

    public function getVoteEndDate(): \DateTime
    {
        return $this->designation->getVoteEndDate();
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getCandidacyPeriodStartDate() <= $now
            && (
                $now < $this->getVoteEndDate()
                || $this->isResultPeriodActive()
            )
        ;
    }

    public function isCandidacyPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getCandidacyPeriodStartDate() <= $now
            && $now < $this->getCandidacyPeriodEndDate()
        ;
    }

    public function isVotePeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getVoteStartDate() <= $now
            && $now < $this->getVoteEndDate()
        ;
    }

    public function isResultPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->designation
            && $this->getVoteEndDate() <= $now
            && $now < (clone $this->getVoteEndDate())->modify(
                sprintf('+%d days', $this->designation->getResultDisplayDelay())
            )
        ;
    }

    public function getAdditionalRoundDuration(): int
    {
        return $this->designation->getAdditionalRoundDuration();
    }
}
