<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Validator\DataSurveyAnswerTypeChoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[DataSurveyAnswerTypeChoice]
#[ORM\Entity]
#[ORM\Table(name: 'jecoute_data_answer')]
class DataAnswer
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Groups(['data_survey_write'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: SurveyQuestion::class, inversedBy: 'dataAnswers')]
    private $surveyQuestion;

    /**
     * @var string|null
     */
    #[Groups(['data_survey_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $textField;

    /**
     * @var Choice[]|Collection
     */
    #[Groups(['data_survey_write'])]
    #[ORM\InverseJoinColumn(name: 'choice_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinColumn(name: 'data_answer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'jecoute_data_answer_selected_choices')]
    #[ORM\ManyToMany(targetEntity: Choice::class, inversedBy: 'dataAnswers')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $selectedChoices;

    /**
     * @var DataSurvey
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: DataSurvey::class, cascade: ['persist', 'remove'], inversedBy: 'answers')]
    private $dataSurvey;

    public function __construct()
    {
        $this->selectedChoices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSurveyQuestion(): ?SurveyQuestion
    {
        return $this->surveyQuestion;
    }

    public function setSurveyQuestion(SurveyQuestion $surveyQuestion): void
    {
        $this->surveyQuestion = $surveyQuestion;
    }

    public function getTextField(): ?string
    {
        return $this->textField;
    }

    public function setTextField(?string $textField): void
    {
        $this->textField = $textField;
    }

    public function getSelectedChoices(): Collection
    {
        return $this->selectedChoices;
    }

    public function addSelectedChoice(Choice $selectedChoice): void
    {
        if (!$this->selectedChoices->contains($selectedChoice)) {
            $this->selectedChoices->add($selectedChoice);
        }
    }

    public function removeSelectedChoice(Choice $selectedChoice): void
    {
        $this->selectedChoices->removeElement($selectedChoice);
    }

    public function getDataSurvey(): DataSurvey
    {
        return $this->dataSurvey;
    }

    public function setDataSurvey(DataSurvey $dataSurvey): void
    {
        $this->dataSurvey = $dataSurvey;
    }
}
