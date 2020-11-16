<?php

namespace App\Entity\VotingPlatform\Designation;

use Doctrine\ORM\Mapping as ORM;

trait EntityElectionHelperTrait
{
    /**
     * @var Designation|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\Designation")
     */
    protected $currentDesignation;

    public function setCurrentElection(ElectionEntityInterface $election): void
    {
        $this->addElection($election);
        $this->setCurrentDesignation($election->getDesignation());
    }

    public function getCurrentDesignation(): ?Designation
    {
        return $this->currentDesignation;
    }

    public function setCurrentDesignation(?Designation $designation): void
    {
        $this->currentDesignation = $designation;
    }

    public function hasActiveElection(): bool
    {
        $election = $this->getCurrentElection();

        return $election && $election->getDesignation()->isActive();
    }

    public function getCurrentElection(): ?ElectionEntityInterface
    {
        if (!$this->currentDesignation) {
            return null;
        }

        foreach ($this->getElections() as $election) {
            if ($election->getDesignation() === $this->currentDesignation) {
                return $election;
            }
        }

        return null;
    }
}
