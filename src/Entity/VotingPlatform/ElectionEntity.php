<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_election_entity")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectionEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Committee|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", cascade={"all"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $committee;

    /**
     * @var Election
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\Election", inversedBy="electionEntity")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

    public function __construct(Committee $committee = null)
    {
        $this->committee = $committee;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }
}
