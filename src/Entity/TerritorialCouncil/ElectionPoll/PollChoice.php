<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_election_poll_choice")
 *
 * @Algolia\Index(autoIndex=false)
 */
class PollChoice
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

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

    public function __construct(Poll $electionPoll, string $value)
    {
        $this->electionPoll = $electionPoll;
        $this->value = $value;
    }
}
