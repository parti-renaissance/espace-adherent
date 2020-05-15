<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\VoteRepository")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Voter", cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $voter;

    /**
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Election")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $votedAt;

    public function __construct(Voter $voter, Election $election)
    {
        $this->voter = $voter;
        $this->election = $election;
        $this->votedAt = new \DateTime();
    }

    public function getVotedAt(): \DateTime
    {
        return $this->votedAt;
    }
}
