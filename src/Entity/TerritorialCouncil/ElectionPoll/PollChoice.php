<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use App\Entity\EntityIdentityTrait;
use App\Repository\TerritorialCouncil\ElectionPoll\PollChoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'territorial_council_election_poll_choice')]
#[ORM\Entity(repositoryClass: PollChoiceRepository::class)]
class PollChoice
{
    use EntityIdentityTrait;

    /**
     * @var Poll
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'choices')]
    private $electionPoll;

    /**
     * @var string
     */
    #[ORM\Column]
    private $value;

    /**
     * @var Vote|Collection
     */
    #[ORM\OneToMany(mappedBy: 'choice', targetEntity: Vote::class, fetch: 'EXTRA_LAZY')]
    private $votes;

    public function __construct(Poll $electionPoll, string $value, ?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->electionPoll = $electionPoll;
        $this->value = $value;

        $this->votes = new ArrayCollection();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }
}
