<?php

namespace App\Entity\Pap;

use ApiPlatform\Core\Annotation\ApiResource;
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
 * @ORM\Entity
 * @ORM\Table(name="pap_campaign_history")
 *
 * @ApiResource(
 *     shortName="PapCampaignHistory",
 *     attributes={
 *         "normalization_context": {
 *             "iri": true,
 *             "groups": {"pap_campaign_history_read"},
 *         },
 *         "denormalization_context": {"groups": {"pap_campaign_history_write"}},
 *         "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_ADHERENT')",
 *     },
 *     collectionOperations={
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
 */
class CampaignHistory implements DataSurveyAwareInterface
{
    use DataSurveyAwareTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $questioner;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pap\Campaign", inversedBy="campaignHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $campaign;

    /**
     * @var string|null
     *
     * @ORM\Column(length=25)
     *
     * @Assert\NotNull
     * @Assert\Choice(
     *     choices=App\Pap\CampaignHistoryStatusEnum::ALL,
     *     message="pap.campaign_history.status.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"pap_campaign_history_read", "pap_campaign_history_write"})
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $building;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", options={"unsigned": true}, nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $floor;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $door;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $firstName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $lastName;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $emailAddress;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true
     * )
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $gender;

    /**
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\AgeRangeEnum", "all"})
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $ageRange;

    /**
     * @ORM\Column(length=30, nullable=true)
     *
     * @Assert\Choice(callback={"App\Jecoute\ProfessionEnum", "all"})
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $profession;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $toContact;

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"pap_campaign_history_write"})
     */
    private $toJoin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    private $finishAt;

    /**
     * @var DataSurvey|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Jecoute\DataSurvey", cascade={"persist"}, orphanRemoval=true, inversedBy="campaignHistory")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Assert\Valid
     */
    private $dataSurvey;

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

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function isFinishedStatus(): bool
    {
        return \in_array($this->status, CampaignHistoryStatusEnum::FINISHED_STATUS);
    }

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(?string $building): void
    {
        $this->building = $building;
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

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }
}
