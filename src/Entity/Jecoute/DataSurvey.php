<?php

namespace App\Entity\Jecoute;

use App\Controller\Api\Jecoute\JemarcheDataSurveyReplyController;
use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\Pap\CampaignHistory as PapCampaignHistory;
use App\Entity\Phoning\CampaignHistory as PhoningCampaignHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_data_survey", indexes={
 *     @ORM\Index(columns={"author_postal_code"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\DataSurveyRepository")
 */
class DataSurvey implements AuthorInterface
{
    use EntityIdentityTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    #[Groups(['survey_replies_list'])]
    private $author;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    #[Groups(['survey_replies_list'])]
    private $authorPostalCode;

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
     * @ORM\OneToMany(targetEntity="App\Entity\Jecoute\DataAnswer", mappedBy="dataSurvey", cascade={"persist", "remove"})
     */
    #[Assert\Valid]
    #[Groups(['data_survey_write'])]
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jecoute\Survey")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'pap_campaign_replies_list', JemarcheDataSurveyReplyController::DESERIALIZE_GROUP, 'pap_campaign_history_read_list'])]
    private $survey;

    /**
     * @var JemarcheDataSurvey|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\JemarcheDataSurvey", mappedBy="dataSurvey")
     */
    private $jemarcheDataSurvey;

    /**
     * @var PhoningCampaignHistory|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Phoning\CampaignHistory", mappedBy="dataSurvey")
     */
    #[Groups(['phoning_campaign_replies_list', 'survey_replies_list'])]
    private $phoningCampaignHistory;

    /**
     * @var PapCampaignHistory|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Pap\CampaignHistory", mappedBy="dataSurvey")
     */
    #[Groups(['pap_campaign_replies_list', 'survey_replies_list'])]
    private $papCampaignHistory;

    public function __construct(Survey $survey = null)
    {
        $this->survey = $survey;
        $this->uuid = Uuid::uuid4();
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

    public function getAuthorPostalCode(): ?string
    {
        return $this->authorPostalCode;
    }

    public function setAuthorPostalCode(?string $authorPostalCode): void
    {
        $this->authorPostalCode = $authorPostalCode;
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

    public function getJemarcheDataSurvey(): ?JemarcheDataSurvey
    {
        return $this->jemarcheDataSurvey;
    }

    public function isOfJemarcheDataSurvey(): bool
    {
        return (bool) $this->jemarcheDataSurvey;
    }

    public function getPhoningCampaignHistory(): ?PhoningCampaignHistory
    {
        return $this->phoningCampaignHistory;
    }

    public function isOfPhoningCampaignHistory(): bool
    {
        return (bool) $this->phoningCampaignHistory;
    }

    public function getPapCampaignHistory(): ?PapCampaignHistory
    {
        return $this->papCampaignHistory;
    }

    public function isOfPapCampaignHistory(): bool
    {
        return (bool) $this->papCampaignHistory;
    }
}
