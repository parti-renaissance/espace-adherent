<?php

namespace App\Entity\LocalElection;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocalElection\LocalElectionRepository")
 */
class LocalElection extends AbstractElectionEntity
{
    /**
     * @var CandidaciesGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocalElection\CandidaciesGroup", mappedBy="election", cascade={"persist"})
     */
    private $candidaciesGroups;

    public function __construct(?Designation $designation = null, ?UuidInterface $uuid = null)
    {
        parent::__construct($designation, $uuid);

        $this->candidaciesGroups = new ArrayCollection();
    }

    /** @return CandidaciesGroup[] */
    public function getCandidaciesGroups(): array
    {
        return $this->candidaciesGroups->toArray();
    }

    public function getLabel(): string
    {
        if (!$designation = $this->getDesignation()) {
            return '';
        }

        return sprintf(
            '%s (%s)',
            $designation->getLabel(),
            implode(', ', $designation->getZonesCodes())
        );
    }

    public function __toString(): string
    {
        return (string) $this->designation?->getLabel();
    }

    public function addCandidaciesGroup(CandidaciesGroup $group): void
    {
        if (!$this->candidaciesGroups->contains($group)) {
            $group->election = $this;
            $this->candidaciesGroups->add($group);
        }
    }

    public function countCandidacies(): int
    {
        return array_sum(array_map(function (CandidaciesGroup $candidaciesGroup) {
            return \count($candidaciesGroup->getCandidacies());
        }, $this->getCandidaciesGroups()));
    }
}
