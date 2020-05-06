<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_vote_result", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_vote", columns={"voter_key", "election_id"}),
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
class VoteResult
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
     * @ORM\Column
     */
    private $voterKey;

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

    /**
     * @var VoteChoice[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\VoteChoice", mappedBy="voteResult", cascade={"all"})
     */
    private $voteChoices;

    public function __construct(Election $election, string $voterKey)
    {
        $this->election = $election;
        $this->voterKey = $voterKey;
        $this->votedAt = new \DateTime();

        $this->voteChoices = new ArrayCollection();
    }

    public function addVoteChoice(VoteChoice $choice): void
    {
        if (!$this->voteChoices->contains($choice)) {
            $choice->setVoteResult($this);
            $this->voteChoices->add($choice);
        }
    }
}
