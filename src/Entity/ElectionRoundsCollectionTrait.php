<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait ElectionRoundsCollectionTrait
{
    /**
     * @var ElectionRound[]|Collection
     */
    private $electionRounds;

    /**
     * @return ElectionRound[]|Collection
     */
    public function getElectionRounds(): Collection
    {
        return $this->electionRounds;
    }

    public function setElectionRounds(iterable $rounds): void
    {
        // Initialize the collection if not done already
        $currentRoundsToRemove = new ArrayCollection($this->electionRounds->toArray());

        foreach ($rounds as $round) {
            if (!$round instanceof ElectionRound) {
                throw new \InvalidArgumentException(sprintf('Expected an instance of "%s", but got "%s".', ElectionRound::class, \is_object($round) ? \get_class($round) : \gettype($round)));
            }

            if ($this->electionRounds->contains($round)) {
                $currentRoundsToRemove->removeElement($round);

                continue;
            }

            $this->electionRounds->add($round);
        }

        foreach ($currentRoundsToRemove as $round) {
            $this->electionRounds->removeElement($round);
        }
    }

    public function addElectionRound(ElectionRound $round): void
    {
        if (!$this->electionRounds->contains($round)) {
            $this->electionRounds->add($round);
        }
    }

    public function removeElectionRound(ElectionRound $round): void
    {
        $this->electionRounds->removeElement($round);
    }

    public function hasElectionRound(ElectionRound $round): bool
    {
        return $this->electionRounds->contains($round);
    }

    public function getElection(): Election
    {
        return $this->electionRounds->current()->getElection();
    }
}
