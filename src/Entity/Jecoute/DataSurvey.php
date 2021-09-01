<?php

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_data_survey")
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\DataSurveyRepository")
 */
class DataSurvey
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $postedAt;

    /**
     * @var DataAnswer[]|Collection
     *
     * @ORM\OneToMany(targetEntity="DataAnswer", mappedBy="dataSurvey", cascade={"persist", "remove"})
     *
     * @Assert\Valid
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotBlank
     */
    private $survey;

    public function __construct(Survey $survey = null)
    {
        $this->survey = $survey;
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getPostedAt(): ?\DateTime
    {
        return $this->postedAt;
    }

    public function addAnswer(DataAnswer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $answer->setDataSurvey($this);
            $this->answers->add($answer);
        }
    }

    public function removeAnswer(DataAnswer $answer): void
    {
        $this->answers->removeElement($answer);
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }
}
