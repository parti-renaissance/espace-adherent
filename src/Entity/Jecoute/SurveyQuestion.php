<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\EntityIdentityTrait;
use App\Repository\Jecoute\SurveyQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SurveyQuestionRepository::class)]
#[ORM\Table(name: 'jecoute_survey_question')]
class SurveyQuestion implements AuthoredInterface
{
    use EntityIdentityTrait;
    use Sortable;

    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var Survey
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Survey::class, cascade: ['persist'], inversedBy: 'questions')]
    private $survey;

    /**
     * @var Question
     */
    #[Assert\Valid]
    #[Groups(['survey_write_dc'])]
    #[ORM\ManyToOne(targetEntity: Question::class, cascade: ['persist'])]
    private $question;

    #[ORM\OneToMany(mappedBy: 'surveyQuestion', targetEntity: DataAnswer::class)]
    private $dataAnswers;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $fromSuggestedQuestion;

    public function __construct(?Survey $survey = null, ?Question $question = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->survey = $survey;
        $this->question = $question;
        $this->dataAnswers = new ArrayCollection();
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
        return $this->getSurvey()->getAuthor();
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

    public function setFromSuggestedQuestion(?int $questionId): void
    {
        $this->fromSuggestedQuestion = $questionId;
    }

    public function getFromSuggestedQuestion(): ?int
    {
        return $this->fromSuggestedQuestion;
    }

    /**
     * @return DataAnswer[]|Collection
     */
    public function getDataAnswers(): Collection
    {
        return $this->dataAnswers;
    }

    /**
     * @return false|DataAnswer
     */
    public function getDataAnswersFor(SurveyQuestion $surveyQuestion, DataSurvey $dataSurvey)
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('surveyQuestion', $surveyQuestion))
            ->andWhere(Criteria::expr()->eq('dataSurvey', $dataSurvey))
        ;

        return $this->dataAnswers->matching($criteria)->first();
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
        $this->refreshUuid();
    }
}
