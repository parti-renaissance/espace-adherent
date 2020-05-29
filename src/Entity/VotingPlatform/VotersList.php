<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_voters_list")
 *
 * @Algolia\Index(autoIndex=false)
 */
class VotersList
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\Election")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

    /**
     * @var Voter[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VotingPlatform\Voter", cascade={"all"}, inversedBy="votersLists")
     * @ORM\JoinTable(name="voting_platform_voters_list_voter", joinColumns={@ORM\JoinColumn(onDelete="CASCADE")})
     */
    private $voters;

    public function __construct(Election $election)
    {
        $this->election = $election;
        $this->voters = new ArrayCollection();
    }

    public function addVoter(Voter $voter): void
    {
        if (!$this->voters->contains($voter)) {
            $this->voters->add($voter);
        }
    }

    /**
     * @return Voter[]
     */
    public function getVoters(): array
    {
        return $this->voters->toArray();
    }
}
