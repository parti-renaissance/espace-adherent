<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityPublishableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="note_question")
 * @ORM\Entity
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Question
{
    use EntityPublishableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Guideline
     *
     * @ORM\ManyToOne(targetEntity="Guideline", inversedBy="questions")
     */
    private $guideline;

    /**
     * @ORM\Column
     */
    private $placeholder;

    /**
     * @ORM\OneToOne(targetEntity="Answer", mappedBy="question")
     */
    private $answer;

    /**
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Gedmo\SortablePosition
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $position = 0;

    /**
     * @ORM\Column
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mandatory;

    public static function create(
        string $name,
        string $placeholder,
        bool $mandatory = false,
        bool $publishable = true
    ): Question {
        $question = new self();

        $question->name = $name;
        $question->placeholder = $placeholder;
        $question->published = $publishable;
        $question->mandatory = $mandatory;

        return $question;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuideline(): Guideline
    {
        return $this->guideline;
    }

    public function setGuideline(Guideline $guideline): void
    {
        $this->guideline = $guideline;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    public function setAnswer(Answer $answer): void
    {
        $this->answer = $answer;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setMandatory(bool $mandatory): void
    {
        $this->mandatory = $mandatory;
    }
}
