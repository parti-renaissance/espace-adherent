<?php

namespace App\Entity\VotingPlatform;

use App\Entity\Committee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'voting_platform_election_entity')]
#[ORM\Entity]
class ElectionEntity
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var Committee|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Committee::class, cascade: ['persist'])]
    private $committee;

    /**
     * @var TerritorialCouncil|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: TerritorialCouncil::class, cascade: ['persist'])]
    private $territorialCouncil;

    /**
     * @var Election
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'electionEntity', targetEntity: Election::class)]
    private $election;

    public function __construct(?Committee $committee = null, ?TerritorialCouncil $council = null)
    {
        $this->committee = $committee;
        $this->territorialCouncil = $council;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getTerritorialCouncil(): ?TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $coTerr): void
    {
        $this->territorialCouncil = $coTerr;
    }

    public function getName(): string
    {
        if ($this->election->getDesignation()->isCopolType()) {
            return $this->territorialCouncil ? $this->territorialCouncil->getName() : '';
        }

        return $this->committee ? $this->committee->getName() : '';
    }
}
