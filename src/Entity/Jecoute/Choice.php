<?php

namespace App\Entity\Jecoute;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_choice")
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\ChoiceRepository")
 */
class Choice
{
    use Sortable;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    #[Groups(['survey_list', 'survey_read_dc'])]
    private $id;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="choices")
     * @Gedmo\SortableGroup
     */
    private $question;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=80)
     */
    #[Groups(['survey_list', 'survey_read_dc', 'survey_write_dc'])]
    private $content;

    /**
     * @var DataAnswer[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Jecoute\DataAnswer", fetch="EXTRA_LAZY", mappedBy="selectedChoices")
     */
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

    public function __toString()
    {
        return $this->content;
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
