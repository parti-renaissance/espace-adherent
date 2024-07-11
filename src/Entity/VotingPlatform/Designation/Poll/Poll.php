<?php

namespace App\Entity\VotingPlatform\Designation\Poll;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'designation_poll')]
#[ORM\Entity]
class Poll
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column]
    #[Assert\NotBlank]
    public ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'poll', targetEntity: PollQuestion::class, cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    private Collection $questions;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->questions = new ArrayCollection();
    }

    /** @return PollQuestion[] */
    public function getQuestions(): array
    {
        return $this->questions->toArray();
    }

    public function addQuestion(PollQuestion $question): void
    {
        if ($this->questions->isEmpty()) {
            $question->setPosition(1);
        }

        if (!$this->questions->contains($question)) {
            $question->poll = $this;
            $this->questions->add($question);
        }
    }

    public function removeQuestion(PollQuestion $question): void
    {
        $this->questions->removeElement($question);
    }

    public function __toString(): string
    {
        return (string) $this->label;
    }
}
