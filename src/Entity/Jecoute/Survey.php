<?php

namespace AppBundle\Entity\Jecoute;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyRepository")
 * @ORM\Table(name="jecoute_survey")

 * @Algolia\Index(autoIndex=false)
 */
class Survey
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $creator;

    /**
     * @var SurveyQuestion[]|Collection
     *
     * @ORM\OneToMany(targetEntity="SurveyQuestion", mappedBy="survey", cascade={"persist", "remove"}, orphanRemoval=true))
     *
     * @Assert\Valid
     */
    private $questions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $published = false;

    public function __construct(Adherent $creator = null, string $name = null, bool $published = false)
    {
        $this->uuid = Uuid::uuid4();
        $this->name = $name;
        $this->creator = $creator;
        $this->published = $published;
        $this->questions = new ArrayCollection();
    }

    public function getCreator(): ?Adherent
    {
        return $this->creator;
    }

    public function setCreator(Adherent $creator = null): void
    {
        $this->creator = $creator;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function removeQuestion(SurveyQuestion $question): void
    {
        $question->setSurvey(null);
        $this->questions->removeElement($question);
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
}
