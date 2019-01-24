<?php

namespace AppBundle\Entity\Jecoute;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthoredInterface;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_survey_question")
 * @ORM\Entity

 * @Algolia\Index(autoIndex=false)
 */
class SurveyQuestion implements AuthoredInterface
{
    use EntityIdentityTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questions", cascade={"persist"})
     * @Gedmo\SortableGroup
     */
    private $survey;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="surveys", cascade={"persist"})
     *
     * @Assert\Valid
     */
    private $question;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $fromSuggestedQuestion = false;

    public function __construct(Survey $survey = null, Question $question = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->survey = $survey;
        $this->question = $question;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->getSurvey()->getAuthor();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function resetSurvey(): void
    {
        $this->survey = null;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function setFromSuggestedQuestion(?bool $fromSuggestedQuestion): void
    {
        $this->fromSuggestedQuestion = (bool) $fromSuggestedQuestion;
    }

    public function isFromSuggestedQuestion(): bool
    {
        return $this->fromSuggestedQuestion;
    }

    public function setUuid(UuidInterface $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function hasUuid(): bool
    {
        return $this->uuid instanceof UuidInterface;
    }
}
