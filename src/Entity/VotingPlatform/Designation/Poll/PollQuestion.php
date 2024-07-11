<?php

namespace App\Entity\VotingPlatform\Designation\Poll;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'designation_poll_question')]
#[ORM\Entity]
class PollQuestion
{
    use EntityIdentityTrait;
    use Sortable;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    public ?string $content = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'questions')]
    public ?Poll $poll = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuestionChoice::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(min: 2)]
    #[Assert\Valid]
    private Collection $choices;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->choices = new ArrayCollection();
    }

    /** @return QuestionChoice[] */
    public function getChoices(): array
    {
        return $this->choices->toArray();
    }

    public function addChoice(QuestionChoice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $choice->question = $this;
            $this->choices->add($choice);
        }
    }

    public function removeChoice(QuestionChoice $choice): void
    {
        $this->choices->removeElement($choice);
    }
}
