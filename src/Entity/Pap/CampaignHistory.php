<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\AdherentIdentityFilter;
use App\Api\Filter\PapCampaignHistoryScopeFilter;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\DataSurveyAwareTrait;
use App\Pap\CampaignHistoryStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Pap\CampaignHistoryRepository")
 * @ORM\Table(name="pap_campaign_history")
 *
 * @ApiResource(
 *     shortName="PapCampaignHistory",
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_campaign_history_read"},
 *         },
 *         "filters": {PapCampaignHistoryScopeFilter::class},
 *         "denormalization_context": {"groups": {"pap_campaign_history_write"}},
 *         "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')",
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/pap_campaign_histories",
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'pap')",
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
 *             "path": "/v3/pap_campaign_histories/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and object.getQuestioner() == user",
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
 * })
 * @ApiFilter(AdherentIdentityFilter::class, properties={"questioner"})
 * @ApiFilter(DateFilter::class, properties={"createdAt", "beginAt"})
 */
class CampaignHistory implements DataSurveyAwareInterface
{
    use DataSurveyAwareTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups({"pap_building_history", "pap_campaign_history_read_list", "pap_campaign_replies_list"})
     */
    private ?Adherent $questioner = null;

    /**
     * @Assert\NotNull
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign", inversedBy="campaignHistories")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_history_read_list"})
     */
    private ?Campaign $campaign = null;

    /**
     * @Assert\NotNull
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Building")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_history_read_list"})
     */
    private ?Building $building = null;

    /**
     * @ORM\Column(length=25)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices=App\Pap\CampaignHistoryStatusEnum::ALL,
     *     message="pap.campaign_history.status.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"pap_campaign_history_read", "pap_campaign_history_write", "pap_campaign_history_read_list"})
     */
    private ?string $status = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write", "pap_building_history", "pap_campaign_history_read_list"})
     */
    private ?string $buildingBlock = null;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true}, nullable=true)
     *
     * @Groups({"pap_campaign_history_write", "pap_building_history", "pap_campaign_history_read_list"})
     */
    private ?int $floor = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write", "pap_building_history", "pap_campaign_history_read_list"})
     */
    private ?string $door = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_replies_list"})
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_replies_list"})
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?string $emailAddress = null;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_replies_list"})
     */
    private ?string $gender = null;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\AgeRangeEnum", "all"})
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?string $ageRange = null;

    /**
     * @ORM\Column(length=30, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\ProfessionEnum", "all"})
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?string $profession = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?bool $toContact = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?string $voterStatus = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?string $voterPostalCode = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private ?bool $toJoin = null;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_history_write", "pap_campaign_replies_list", "survey_replies_list"})
     */
    private ?\DateTime $beginAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     *
     * @Groups({"pap_campaign_replies_list", "survey_replies_list"})
     */
    private ?\DateTime $finishAt = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\DataSurvey", cascade={"persist"}, orphanRemoval=true, inversedBy="papCampaignHistory")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private ?DataSurvey $dataSurvey = null;

    public function __construct(UuidInterface $uuid = null)
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

    /**
     * @Groups({"pap_building_history"})
     */
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

    /**
     * @Groups({"pap_campaign_history_read_list", "pap_campaign_replies_list"})
     */
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
