<?php

namespace App\Entity\Jecoute;

use App\Repository\Jecoute\ChoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChoiceRepository::class)]
#[ORM\Table(name: 'jecoute_choice')]
class Choice
{
    use Sortable;

    #[Groups(['survey_list', 'survey_read_dc'])]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Question
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'choices')]
    private $question;

    #[Assert\Length(max: 80)]
    #[Assert\NotBlank]
    #[Groups(['survey_list', 'survey_read_dc', 'survey_write_dc'])]
    #[ORM\Column]
    private $content;

    /**
     * @var DataAnswer[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: DataAnswer::class, mappedBy: 'selectedChoices', fetch: 'EXTRA_LAZY')]
    private $dataAnswers;

    public function __construct(?string $content = null)
    {
        $this->content = $content;
        $this->dataAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return (string) $this->content;
    }

    /**
     * @return DataAnswer[]|Collection
     */
    public function getDataAnswers(): Collection
    {
        return $this->dataAnswers;
    }

    public function addDataAnswer(DataAnswer $dataAnswer): void
    {
        if (!$this->dataAnswers->contains($dataAnswer)) {
            $this->dataAnswers->add($dataAnswer);
        }
    }

    public function removeDataAnswer(DataAnswer $dataAnswer): void
    {
        $this->dataAnswers->removeElement($dataAnswer);
    }

    public function __clone()
    {
        $this->resetId();
    }
}
