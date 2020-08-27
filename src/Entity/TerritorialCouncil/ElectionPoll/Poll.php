<?php

namespace App\Entity\TerritorialCouncil\ElectionPoll;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\TerritorialCouncil\Election;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="territorial_council_election_poll")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Poll
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $gender;

    /**
     * @var PollChoice[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TerritorialCouncil\ElectionPoll\PollChoice", mappedBy="electionPoll", cascade={"all"})
     */
    private $choices;

    /**
     * @var Election
     *
     * @ORM\OneToOne(targetEntity="App\Entity\TerritorialCouncil\Election", mappedBy="electionPoll")
     */
    private $election;

    public function __construct(string $gender, UuidInterface $uuid = null)
    {
        $this->gender = $gender;
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->choices = new ArrayCollection();
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function addChoice(PollChoice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
        }
    }

    /**
     * @return PollChoice[]
     */
    public function getChoices(): array
    {
        return $this->choices->toArray();
    }
}
