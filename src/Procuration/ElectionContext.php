<?php

namespace App\Procuration;

use App\Entity\Election;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ElectionContext
{
    public const ACTION_PROPOSAL = 'proposition';
    public const ACTION_REQUEST = 'demande';

    public const CONTROLLER_ACTION_REQUIREMENT = self::ACTION_REQUEST.'|'.self::ACTION_PROPOSAL;

    #[Assert\Count(min: 1, minMessage: 'procuration.election_context.min_count')]
    private $elections = [];

    private $cachedElectionIds = [];

    /**
     * @return int[]
     */
    public function getCachedIds(): ?array
    {
        return $this->cachedElectionIds;
    }

    /**
     * @return Election[]|iterable
     */
    public function getElections(): iterable
    {
        return $this->elections;
    }

    public function getElection(): ?Election
    {
        return !empty($this->elections)
            ? reset($this->elections)
            : null;
    }

    public function setElection(Election $election): void
    {
        $this->elections = [$election];
    }

    /**
     * @param Election[]|iterable $elections
     */
    public function setElections(iterable $elections): void
    {
        foreach ($elections as $election) {
            if (!$election instanceof Election) {
                throw new \InvalidArgumentException(sprintf('Expected an instance of "%s", but got "%s".', Election::class, \is_object($election) ? $election::class : \gettype($election)));
            }
        }

        $this->elections = $elections;
    }

    public function __serialize(): array
    {
        return array_map(function (Election $election) {
            return $election->getId();
        }, $this->elections instanceof Collection ? $this->elections->toArray() : $this->elections);
    }

    public function __unserialize(array $serialized): void
    {
        $this->cachedElectionIds = $serialized;
    }
}
