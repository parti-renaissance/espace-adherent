<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\ElectionPoll\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'territorial_council_election_poll_vote')]
#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    protected $id;

    /**
     * @var PollChoice
     */
    #[ORM\ManyToOne(targetEntity: PollChoice::class, inversedBy: 'votes')]
    private $choice;

    /**
     * @var TerritorialCouncilMembership
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: TerritorialCouncilMembership::class)]
    private $membership;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct(PollChoice $choice, TerritorialCouncilMembership $membership)
    {
        $this->choice = $choice;
        $this->membership = $membership;
        $this->createdAt = new \DateTime();
    }

    public function getChoice(): PollChoice
    {
        return $this->choice;
    }
}
