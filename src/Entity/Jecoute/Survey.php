<?php

namespace AppBundle\Entity\Jecoute;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthoredInterface;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Jecoute\SurveyRepository")
 * @ORM\Table(name="jecoute_survey")

 * @Algolia\Index(autoIndex=false)
 */
class Survey implements AuthoredInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @JMS\Groups({"survey_list"})
     */
    private $name;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     *
     * @JMS\Groups({"survey_list"})
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @var SurveyQuestion[]|Collection
     *
     * @ORM\OneToMany(targetEntity="SurveyQuestion", mappedBy="survey", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     */
    private $questions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $published = false;

    public function __construct(
        Adherent $author = null,
        string $name = null,
        string $city = null,
        bool $published = false
    ) {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->city = $city;
        $this->author = $author;
        $this->published = $published;
        $this->questions = new ArrayCollection();
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function refreshUuid(): void
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author = null): void
    {
        $this->author = $author;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function addQuestion(SurveyQuestion $surveyQuestion): void
    {
        if (!$this->questions->contains($surveyQuestion)) {
            $surveyQuestion->setSurvey($this);
            $this->questions->add($surveyQuestion);
        }
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function setQuestions(ArrayCollection $surveyQuestions): void
    {
        $this->questions = $surveyQuestions;
    }

    public function removeQuestion(SurveyQuestion $surveyQuestion): void
    {
        $surveyQuestion->resetSurvey();
        $this->questions->removeElement($surveyQuestion);
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function questionsCount(): int
    {
        return \count($this->questions);
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("id"),
     * @JMS\Groups({"survey_list"})
     */
    public function getExposedId(): int
    {
        return $this->id;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("questions")
     * @JMS\Groups({"survey_list"})
     */
    public function getQuestionsAsArray(): array
    {
        return array_map(function (SurveyQuestion $surveyQuestion) {
            $question = $surveyQuestion->getQuestion();

            return [
                'id' => $surveyQuestion->getId(),
                'type' => $question->getType(),
                'content' => $question->getContent(),
                'choices' => $question->getChoices(),
            ];
        }, $this->questions->toArray());
    }

    public function __clone()
    {
        if ($this->id) {
            $this->resetId();
            $this->refreshUuid();
            $this->setPublished(false);
            $this->setName($this->name.' (Copie)');

            $questions = new ArrayCollection();
            foreach ($this->getQuestions() as $surveyQuestion) {
                $clonedSurveyQuestion = clone $surveyQuestion;
                $clonedSurveyQuestion->setSurvey($this);

                if (!$surveyQuestion->isFromSuggestedQuestion()) {
                    $clonedQuestion = clone $surveyQuestion->getQuestion();
                    $clonedSurveyQuestion->setQuestion($clonedQuestion);
                }

                $questions->add($clonedSurveyQuestion);
            }

            $this->setQuestions($questions);
        }
    }
}
