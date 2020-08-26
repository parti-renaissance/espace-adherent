<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TerritorialCouncil\ElectionPoll\PollChoiceRepository")
 * @ORM\Table(name="territorial_council_election_poll_choice")
 *
 * @Algolia\Index(autoIndex=false)
 */
class PollChoice
{
    use EntityIdentityTrait;

    /**
     * @var Poll
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\ElectionPoll\Poll", inversedBy="choices")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $electionPoll;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $value;

    public function __construct(Poll $electionPoll, string $value, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->electionPoll = $electionPoll;
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
