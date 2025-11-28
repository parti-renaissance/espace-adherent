<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Controller\Api\Jecoute\JemarcheDataSurveyReplyController;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\Pap\CampaignHistory as PapCampaignHistory;
use App\Entity\Phoning\CampaignHistory as PhoningCampaignHistory;
use App\Repository\Jecoute\DataSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DataSurveyRepository::class)]
#[ORM\Index(columns: ['author_postal_code'])]
#[ORM\Table(name: 'jecoute_data_survey')]
class DataSurvey implements AuthorInterface
{
    use EntityIdentityTrait;
    use AuthoredTrait;

    /**
     * @var string|null
     */
    #[Groups(['survey_replies_list'])]
    #[ORM\Column(nullable: true)]
    private $authorPostalCode;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private $postedAt;

    /**
     * @var DataAnswer[]|Collection
     */
    #[Assert\Valid]
    #[Groups(['data_survey_write'])]
    #[ORM\OneToMany(mappedBy: 'dataSurvey', targetEntity: DataAnswer::class, cascade: ['persist', 'remove'])]
    private $answers;

    #[Assert\NotBlank]
    #[Groups(['phoning_campaign_history_read_list', 'phoning_campaign_replies_list', 'pap_campaign_replies_list', JemarcheDataSurveyReplyController::DESERIALIZE_GROUP, 'pap_campaign_history_read_list'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Survey::class)]
    private $survey;

    #[ORM\OneToOne(mappedBy: 'dataSurvey')]
    private ?JemarcheDataSurvey $jemarcheDataSurvey = null;

    #[Groups(['data_survey_write', 'phoning_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\OneToOne(mappedBy: 'dataSurvey')]
    private ?PhoningCampaignHistory $phoningCampaignHistory = null;

    #[Groups(['data_survey_write', 'pap_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\OneToOne(mappedBy: 'dataSurvey')]
    private ?PapCampaignHistory $papCampaignHistory = null;

    public function __construct(?Survey $survey = null)
    {
        $this->survey = $survey;
        $this->uuid = Uuid::uuid4();
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setPhoningCampaignHistory(?PhoningCampaignHistory $phoningCampaignHistory): void
    {
        $this->phoningCampaignHistory = $phoningCampaignHistory;
        $this->phoningCampaignHistory->setDataSurvey($this);
    }

    public function setPapCampaignHistory(?PapCampaignHistory $papCampaignHistory): void
    {
        $this->papCampaignHistory = $papCampaignHistory;
        $this->papCampaignHistory->setDataSurvey($this);
    }
}
