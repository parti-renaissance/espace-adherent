<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_voters_list')]
class VotersList
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Election
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'votersList', targetEntity: Election::class)]
    private $election;

    /**
     * @var Voter[]|ArrayCollection
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'voting_platform_voters_list_voter')]
    #[ORM\ManyToMany(targetEntity: Voter::class, inversedBy: 'votersLists', cascade: ['all'], fetch: 'EXTRA_LAZY')]
    private $voters;

    public function __construct(Election $election)
    {
        $this->election = $election;
        $this->voters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function countVoters(): int
    {
        return $this->voters->count();
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function removeVoter(Voter $voter): void
    {
        $this->voters->removeElement($voter);
    }
}
