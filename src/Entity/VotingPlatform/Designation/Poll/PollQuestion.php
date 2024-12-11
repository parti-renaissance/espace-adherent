<?php

namespace App\Entity\VotingPlatform\Designation\Poll;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'designation_poll_question')]
class PollQuestion
{
    use EntityIdentityTrait;
    use Sortable;

    #[Assert\Length(max: 500, groups: ['api_designation_write'])]
    #[Assert\NotBlank]
    #[Groups(['designation_read'])]
    #[ORM\Column(length: 500)]
    public ?string $content = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Poll::class, inversedBy: 'questions')]
    public ?Poll $poll = null;

    #[Assert\Count(min: 2)]
    #[Assert\Valid]
    #[Groups(['designation_read'])]
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuestionChoice::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $choices;

    public function __construct(?string $content = null)
    {
        $this->content = $content;
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
