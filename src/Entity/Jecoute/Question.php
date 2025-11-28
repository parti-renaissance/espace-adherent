<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Jecoute\SurveyQuestionTypeEnum;
use App\Validator\SurveyQuestionTypeChoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\InheritanceType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[DiscriminatorColumn(name: 'discr', type: 'string')]
#[DiscriminatorMap(['question' => Question::class, 'suggested_question' => SuggestedQuestion::class])]
#[InheritanceType('JOINED')]
#[ORM\Entity]
#[ORM\Table(name: 'jecoute_question')]
#[SurveyQuestionTypeChoice]
class Question
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['survey_list', 'survey_read_dc', 'survey_write_dc'])]
    #[ORM\Column]
    private $content;

    #[Assert\Choice(callback: [SurveyQuestionTypeEnum::class, 'all'])]
    #[Assert\NotBlank]
    #[Groups(['survey_list', 'survey_read_dc', 'survey_write_dc'])]
    #[ORM\Column]
    private $type;

    /**
     * @var Choice[]|Collection
     */
    #[Assert\Valid]
    #[Groups(['survey_list', 'survey_read_dc', 'survey_write_dc'])]
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Choice::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $choices;

    public function __construct(?string $content = null, ?string $type = null)
    {
        $this->content = $content;
        $this->type = $type ?? SurveyQuestionTypeEnum::SIMPLE_FIELD;
        $this->choices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTypeLabel(): string
    {
        return array_flip(SurveyQuestionTypeEnum::all())[$this->type];
    }

    public function getChoicesAsJson(): string
    {
        return json_encode(array_map(function (Choice $choice) {
            return $choice->getContent();
        }, $this->choices->toArray()));
    }

    public function addChoice(Choice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $choice->setQuestion($this);
            $this->choices->add($choice);
        }
    }

    public function removeChoice(Choice $choice): void
    {
        $this->choices->removeElement($choice);
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function setChoices(Collection $choices): void
    {
        $this->choices = $choices;
    }

    public function isChoiceType(): bool
    {
        return \in_array($this->type, [
            SurveyQuestionTypeEnum::UNIQUE_CHOICE_TYPE,
            SurveyQuestionTypeEnum::MULTIPLE_CHOICE_TYPE,
        ], true);
    }

    public function getChoicesOrdered(): Collection
    {
        return $this->choices->matching(Criteria::create()->orderBy(['position' => 'ASC']));
    }

    public function __clone()
    {
        $this->resetId();

        $choices = new ArrayCollection();
        foreach ($this->getChoices() as $choice) {
            /** @var Choice $choice */
            $clonedChoice = clone $choice;
            $clonedChoice->setQuestion($this);

            $choices->add($clonedChoice);
        }

        $this->setChoices($choices);
    }
}
