<?php

namespace AppBundle\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VotingPlatform\VoteRepository")
 *
 * @ORM\Table(name="voting_platform_vote", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_vote", columns={"voter_id", "election_id"}),
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
class Vote
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
     * @var Voter
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VotingPlatform\Voter", inversedBy="votes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $voter;

    /**
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VotingPlatform\Election")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

    /**
     * @var CandidateGroup
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\VotingPlatform\CandidateGroup")
     * @ORM\JoinTable(name="voting_platform_vote_candidate_group", joinColumns={@ORM\JoinColumn(onDelete="CASCADE")})
     */
    private $candidateGroups;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $votedAt;

    public function __construct(Election $election)
    {
        $this->election = $election;
        $this->votedAt = new \DateTime();

        $this->candidateGroups = new ArrayCollection();
    }

    public function addCandidateGroup(CandidateGroup $group): void
    {
        if (!$this->candidateGroups->contains($group)) {
            $this->candidateGroups->add($group);
        }
    }

    public function setVoter(Voter $voter): void
    {
        $this->voter = $voter;
    }
}
