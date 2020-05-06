<?php

namespace App\Procuration;

use App\Entity\Election;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ElectionContext implements \Serializable
{
    public const ACTION_PROPOSAL = 'proposition';
    public const ACTION_REQUEST = 'demande';

    public const CONTROLLER_ACTION_REQUIREMENT = self::ACTION_REQUEST.'|'.self::ACTION_PROPOSAL;

    /**
     * @Assert\Count(min=1, minMessage="procuration.election_context.min_count")
     */
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

    /**
     * @param Election[]|iterable $elections
     */
    public function setElections(iterable $elections): void
    {
        foreach ($elections as $election) {
            if (!$election instanceof Election) {
                throw new \InvalidArgumentException(sprintf('Expected an instance of "%s", but got "%s".', Election::class, \is_object($election) ? \get_class($election) : \gettype($election)));
            }
        }

        $this->elections = $elections;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(\array_map(function (Election $election) {
            return $election->getId();
        }, $this->elections instanceof Collection ? $this->elections->toArray() : $this->elections));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->cachedElectionIds = \unserialize($serialized);
    }
}
