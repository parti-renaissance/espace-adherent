<?php

namespace App\Entity;

use App\Entity\VotingPlatform\Designation\AbstractElectionEntity;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeElectionRepository")
 */
class CommitteeElection extends AbstractElectionEntity
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", inversedBy="committeeElections")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committee;

    /**
     * @var CommitteeCandidacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeCandidacy", mappedBy="committeeElection")
     */
    private $candidacies;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $adherentNotified = false;

    public function __construct(Designation $designation = null)
    {
        parent::__construct($designation);

        $this->candidacies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function countCandidacies(): int
    {
        return $this->candidacies->count();
    }

    public function setAdherentNotified(bool $adherentNotified): void
    {
        $this->adherentNotified = $adherentNotified;
    }
}
