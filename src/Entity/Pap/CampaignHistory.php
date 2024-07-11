<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\AdherentIdentityFilter;
use App\Api\Filter\PapCampaignHistoryScopeFilter;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\DataSurveyAwareTrait;
use App\Jecoute\AgeRangeEnum;
use App\Jecoute\ProfessionEnum;
use App\Pap\CampaignHistoryStatusEnum;
use App\Repository\Pap\CampaignHistoryRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     shortName="PapCampaignHistory",
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_campaign_history_read"},
 *         },
 *         "filters": {PapCampaignHistoryScopeFilter::class},
 *         "denormalization_context": {"groups": {"pap_campaign_history_write"}},
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')",
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/pap_campaign_histories",
 *             "security": "is_granted('IS_FEATURE_GRANTED', ['pap_v2', 'pap'])",
 *             "normalization_context": {
 *                 "groups": {"pap_campaign_history_read_list"}
 *             },
 *         },
 *         "post": {
 *             "path": "/v3/pap_campaign_histories",
 *         },
 *     },
 *     itemOperations={
 *         "put": {
 *             "path": "/v3/pap_campaign_histories/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and object.getQuestioner() == user",
 *         },
 *         "post_reply": {
 *             "method": "POST",
 *             "path": "/v3/pap_campaign_histories/{uuid}/reply",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Pap\CampaignHistoryReplyController",
 *             "defaults": {"_api_receive": false},
 *             "normalization_context": {"groups": {"data_survey_read"}},
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "status": "exact",
 *     "campaign.uuid": "exact"
 * })
 * @ApiFilter(AdherentIdentityFilter::class, properties={"questioner"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "beginAt"})
 * @ApiFilter(OrderFilter::class, properties={"createdAt"})
 */
#[ORM\Table(name: 'pap_campaign_history')]
#[ORM\Entity(repositoryClass: CampaignHistoryRepository::class)]
class CampaignHistory implements DataSurveyAwareInterface
{
    use DataSurveyAwareTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Groups(['pap_building_history', 'pap_campaign_history_read_list', 'pap_campaign_replies_list'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private ?Adherent $questioner = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_history_read_list'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Campaign::class, inversedBy: 'campaignHistories')]
    #[Assert\NotNull]
    private ?Campaign $campaign = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_history_read_list'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Building::class)]
    #[Assert\NotNull]
    private ?Building $building = null;

    #[Groups(['pap_campaign_history_read', 'pap_campaign_history_write', 'pap_campaign_history_read_list'])]
    #[ORM\Column(length: 25)]
    #[Assert\NotNull]
    #[Assert\Choice(choices: CampaignHistoryStatusEnum::ALL, message: 'pap.campaign_history.status.invalid_choice')]
    private ?string $status = null;

    #[Groups(['pap_campaign_history_write', 'pap_building_history', 'pap_campaign_history_read_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $buildingBlock = null;

    #[Groups(['pap_campaign_history_write', 'pap_building_history', 'pap_campaign_history_read_list'])]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private ?int $floor = null;

    #[Groups(['pap_campaign_history_write', 'pap_building_history', 'pap_campaign_history_read_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $door = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $firstName = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $lastName = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    private ?string $emailAddress = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice')]
    private ?string $gender = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Choice(callback: [AgeRangeEnum::class, 'all'])]
    private ?string $ageRange = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Choice(callback: [ProfessionEnum::class, 'all'])]
    private ?string $profession = null;

    #[Groups(['pap_campaign_history_write'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $toContact = null;

    #[Groups(['pap_campaign_history_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $voterStatus = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list'])]
    #[ORM\Column(nullable: true)]
    private ?string $voterPostalCode = null;

    #[Groups(['pap_campaign_history_write'])]
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $toJoin = null;

    #[Groups(['pap_campaign_history_write', 'pap_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $beginAt = null;

    #[Groups(['pap_campaign_replies_list', 'survey_replies_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $finishAt = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\OneToOne(inversedBy: 'papCampaignHistory', targetEntity: DataSurvey::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Valid]
    private ?DataSurvey $dataSurvey = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getQuestioner(): ?Adherent
    {
        return $this->questioner;
    }

    public function setQuestioner(?Adherent $questioner): void
    {
        $this->questioner = $questioner;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): void
    {
        $this->building = $building;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    #[Groups(['pap_building_history'])]
    public function getStatusLabel(): ?string
    {
        return CampaignHistoryStatusEnum::LABELS[$this->status] ?? null;
    }

    public function isFinishedStatus(): bool
    {
        return \in_array($this->status, CampaignHistoryStatusEnum::FINISHED_STATUS);
    }

    public function getBuildingBlock(): ?string
    {
        return $this->buildingBlock;
    }

    public function setBuildingBlock(?string $buildingBlock): void
    {
        $this->buildingBlock = $buildingBlock;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(?int $floor): void
    {
        $this->floor = $floor;
    }

    public function getDoor(): ?string
    {
        return $this->door;
    }

    public function setDoor(?string $door): void
    {
        $this->door = $door;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getAgeRange(): ?string
    {
        return $this->ageRange;
    }

    public function setAgeRange(?string $ageRange): void
    {
        $this->ageRange = $ageRange;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): void
    {
        $this->profession = $profession;
    }

    public function getVoterStatus(): ?string
    {
        return $this->voterStatus;
    }

    public function setVoterStatus(?string $voterStatus): void
    {
        $this->voterStatus = $voterStatus;
    }

    public function getVoterPostalCode(): ?string
    {
        return $this->voterPostalCode;
    }

    public function setVoterPostalCode(?string $voterPostalCode): void
    {
        $this->voterPostalCode = $voterPostalCode;
    }

    public function isToContact(): ?bool
    {
        return $this->toContact;
    }

    public function setToContact(?bool $toContact): void
    {
        $this->toContact = $toContact;
    }

    public function isToJoin(): ?bool
    {
        return $this->toJoin;
    }

    public function setToJoin(?bool $toJoin): void
    {
        $this->toJoin = $toJoin;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeInterface $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    #[Groups(['pap_campaign_history_read_list', 'pap_campaign_replies_list'])]
    public function getDuration(): int
    {
        return $this->finishAt ? $this->finishAt->getTimestamp() - $this->beginAt->getTimestamp() : 0;
    }

    public function isDoorOpenStatus(): bool
    {
        return CampaignHistoryStatusEnum::DOOR_OPEN === $this->status;
    }

    public function isContactLaterStatus(): bool
    {
        return CampaignHistoryStatusEnum::CONTACT_LATER === $this->status;
    }
}
